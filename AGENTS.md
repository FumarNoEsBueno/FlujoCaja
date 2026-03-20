# AGENTS.md — FlujoCaja

> Guía para agentes de código (y humanos) que trabajen en este repositorio.
> Priorizá siempre el código humano existente como fuente de verdad sobre estilo y patrones.

---

## Stack

- **PHP 8.2+** con Laravel 12
- **JWT Auth** (`tymon/jwt-auth ^2.3`) — guard `api`
- **Laravel Sanctum** instalado pero el guard activo es `api` con JWT
- **SQLite** en tests (`:memory:`), MySQL/MariaDB en producción
- **Laravel Pint** para formateo de código
- **PHPUnit 11** para tests

---

## Comandos

### Desarrollo

```bash
composer dev          # servidor + queue + logs + vite en paralelo
php artisan serve     # solo el servidor HTTP
npm run dev           # solo Vite
```

### Setup inicial

```bash
composer setup        # install + .env + key:generate + migrate + npm install + build
```

### Tests

```bash
composer test                                    # config:clear + php artisan test
php artisan test                                 # todos los tests
php artisan test tests/Feature/ExampleTest.php   # un archivo específico
php artisan test --filter NombreDelTest          # un test por nombre
php artisan test --testsuite Feature             # solo Feature tests
php artisan test --testsuite Unit                # solo Unit tests
```

### Linting / Formateo

```bash
./vendor/bin/pint                  # formatear todo el proyecto
./vendor/bin/pint app/             # formatear solo app/
./vendor/bin/pint --test           # verificar sin modificar (CI)
```

### Migraciones y Seeders

```bash
php artisan migrate                              # correr migraciones
php artisan migrate:fresh --seed                 # reiniciar DB con seeders
php artisan db:seed --class=NombreSeeder         # un seeder específico
```

### Generación de código

```bash
php artisan make:repository NombreDominio        # genera Interface + Eloquent + DTO
php artisan make:repository NombreDominio --no-dto  # sin DTO
```

---

## Arquitectura: Repository Pattern por dominio

Cada dominio tiene su propia carpeta bajo `app/Repositories/{Dominio}/`:

```
app/Repositories/
└── Auth/
│   ├── DTOs/           ← DTOs (final readonly class)
│   ├── Eloquent/       ← implementación concreta
│   └── Interfaces/     ← contrato
└── Movimiento/
    ├── DTOs/
    ├── Eloquent/
    └── Interfaces/
```

**Flujo obligatorio:** `Request → Controller → DTO → Repository → Model`

- Los controllers NUNCA acceden a Eloquent directamente
- Los repositories NO conocen la lógica de negocio compleja
- Los DTOs son `final readonly class` — inmutables, tipados, construibles desde `fromRequest()`

### Registro de bindings

Todos los bindings Interface → Implementación van en `app/Providers/RepositoryServiceProvider.php`:

```php
$this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
```

---

## Convenciones de código

### PHP general

```php
declare(strict_types=1);  // OBLIGATORIO en todos los archivos PHP
```

- PHP 8.2+ features: `readonly`, `match`, `named arguments`, `nullsafe operator`
- `final readonly class` para DTOs
- Constructor property promotion siempre
- Type hints en todo: parámetros, retornos, propiedades

### Imports

- Un `use` por línea, sin agrupaciones
- Ordenados: primero clases de la app, luego vendor, luego PHP nativo
- Pint maneja el orden automáticamente — no lo hagas a mano

### Naming conventions

| Elemento | Convención | Ejemplo |
|---|---|---|
| Clases | PascalCase | `MovimientoRepository` |
| Métodos/variables | camelCase | `fromRequest()`, `$tipoMovimiento` |
| Columnas DB | `{prefijo}_{nombre}` | `movi_monto_total`, `usua_rut` |
| Tablas DB | snake_case plural | `movimientos`, `usuarios` |
| Interfaces | sufijo `Interface` | `MovimientoRepositoryInterface` |
| DTOs | prefijo de acción | `StoreMovimientoDTO`, `LoginDTO` |
| Resources | sufijo `Resource` | `MovimientoResource` |
| Requests | sufijo `Request` | `StoreMovimientoRequest` |

**Prefijos de columnas por tabla:**
- `movi_` → movimientos
- `usua_` → usuarios
- `caja_` → cajas
- `prod_` → productos
- `timo_` → tipo_movimiento
- `perm_` → permisos
- `role_` → roles
- `pdmo_` → productos_del_movimiento

### Separadores visuales en clases grandes

Usá el separador de sección de Pint para agrupar lógicamente:

```php
// ─── Helpers privados ─────────────────────────────────────────────────────
```

---

## Respuestas HTTP (ApiResponser trait)

**TODOS** los controllers usan el trait `App\Http\Traits\ApiResponser`. Nunca uses `response()->json()` directamente en un controller.

```php
// Éxito
return $this->successResponse(data: $data, message: 'Mensaje.', statusCode: 200);

// Éxito con paginación
return $this->paginatedResponse(data: $items, meta: [...], message: 'Mensaje.');

// Error
return $this->errorResponse(
    message: 'Mensaje para el cliente.',
    statusCode: 500,
    exception: $e,       // se loguea, NO se expone al cliente
    method: __METHOD__,  // para trazabilidad en logs
);
```

**Estructura de respuesta estándar:**
```json
{ "success": true|false, "message": "...", "data": ... }
```

---

## Manejo de errores

Patrón obligatorio en todos los métodos de controller:

```php
public function show(int $id): JsonResponse
{
    try {
        $resultado = $this->repository->show($id);
        return $this->successResponse(data: $resultado, message: 'OK.');
    } catch (Exception $e) {
        return $this->errorResponse(
            message: 'Mensaje genérico para el cliente.',
            statusCode: 500,
            exception: $e,
            method: __METHOD__,
        );
    }
}
```

- Nunca expongas mensajes internos de excepción al cliente
- El log lo hace `ApiResponser::errorResponse()` automáticamente — no hagas `Log::error()` adicional en el controller (salvo casos especiales como streaming)
- Usá `RuntimeException` en repositories cuando algo falla internamente

---

## DTOs

```php
final readonly class StoreMovimientoDTO
{
    public function __construct(
        public int    $cajaId,
        public string $descripcion,
        public float  $montoTotal,
    ) {}

    public static function fromRequest(StoreMovimientoRequest $request, int $usuaId): self
    {
        return new self(
            cajaId:      $request->caja_id,
            descripcion: $request->descripcion,
            montoTotal:  $request->monto_total,
        );
    }
}
```

---

## Models

- Siempre declarar `protected $table` explícitamente
- `$fillable` exhaustivo — no usar `$guarded = []`
- `$hidden` para campos sensibles (`usua_password`)
- `$casts` para fechas y tipos numéricos
- Relaciones con nombre en camelCase que refleje el modelo relacionado: `tipoMovimiento()`, `productosDelMovimiento()`
- Los métodos de relación no llevan docblock salvo que el tipo no sea inferible

---

## Tests

- Los tests de Feature usan SQLite `:memory:` (configurado en `phpunit.xml`)
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` — no tocar estas variables en tests
- `APP_ENV=testing` — nunca mockear el env en el test, ya está configurado

---

## Rutas

- Siempre con `name()` descriptivo
- Agrupadas por dominio con `prefix()` y `name()`
- Las rutas específicas van ANTES que las de parámetro dinámico:
  ```php
  Route::get('autocomplete', ...)->name('autocomplete');  // primero
  Route::get('{id}', ...)->name('show');                  // después
  ```

---

## Columnas especiales y dominio de negocio

- **RUT chileno**: separado en `usua_rut` (int) y `usua_dv` (string). Usar `RutHelper::parse()` para normalizar
- **Autenticación**: guard `api` con JWT — siempre `Auth::guard('api')`, nunca `auth()->user()` directamente
- **Movimientos**: tienen verificación de montos — `movi_monto_total` incluye propina, la lógica de verificación vive en el Model
- **Permisos**: cargados desde `rol.permisosActivos` (relación con scope)
