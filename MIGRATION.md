# Migración a Vue 3 + Inertia.js — SAINS 2026

Estado: **panel admin COMPLETO** y **panel de estudiante COMPLETO** (migrados + rediseñados).
Pendiente sólo `auth/reset-password` (público) e i18n transversal.

## Stack de UI

Los paneles **admin y estudiante** usan **Ant Design Vue 4** (`ant-design-vue`, `@ant-design/icons-vue`).
Auto-import de componentes vía `unplugin-vue-components` + `AntDesignVueResolver` (solo se
empaqueta lo que se usa; los estilos van por CSS-in-JS en runtime, solo se importa el reset).
Tema en `resources/js/theme.js` (color primario `#4f46e5`). Helpers en `resources/js/lib/notify.js`
(`showFlash`, `confirmDelete`, `confirmAction`, `message`) — reemplazan a SweetAlert2.

### Sistema de diseño (pase de "modo diseñador")

- `resources/css/app.css` — capa de diseño global: tokens, tarjetas, tablas, scrollbars,
  transición de página (`.sains-page`), toasts, encabezados. Se importa en `app.js`.
- `resources/js/theme.js` — tokens de Ant Design (sombras suaves, radios, densidad, colores por componente).
- **Componentes compartidos**:
  - `Components/PageHead.vue` — encabezado de página (icono en degradado + título + subtítulo + slot `#actions` + `back`).
  - `Components/StatCard.vue` — tarjeta de estadística con barra de acento por color e icono opcional
    (`color`: indigo|violet|green|pink|amber|slate|red). Se usan en grid `.sains-stats.sains-stats--{2,3,4}`.
- `resources/js/lib/useIndex.js` — `useIndex()` (filtros reactivos + debounce + partial reloads
  con `only` + `reset()`) y `laravelPagination()` (paginador Laravel → objeto AntD).
- `app.js` maneja el error de "chunk viejo tras deploy" (`vite:preloadError` + `router.on('exception')` → reload).

La **landing pública** (`Welcome`, `Terms`) sigue con Bootstrap 5 (grid + utilidades) por su
diseño de marketing; el `AuthModal` sí usa Ant Design.

⚠️ Ant Design Vue: **no uses `@finish` en `<a-form>`** para disparar el submit — no dispara de
forma fiable con Inertia `useForm`. Usa `@click` en el botón (ver `AuthModal.vue`).

## Qué ya quedó configurado

| Pieza | Archivo |
|---|---|
| Dependencias PHP | `inertiajs/inertia-laravel ^2`, `tightenco/ziggy ^2` |
| Dependencias JS | `vue ^3.5`, `@inertiajs/vue3 ^2`, `ant-design-vue ^4.2`, `@ant-design/icons-vue ^7`, `unplugin-vue-components`, `ziggy-js`, `laravel-vite-plugin ^1`, `vite ^6` |
| Bootstrap de Vue/Inertia | `resources/js/app.js` |
| Plantilla raíz | `resources/views/app.blade.php` |
| Props compartidos (auth.user / auth.estudiante / auth.admin / flash / ziggy) | `app/Http/Middleware/HandleInertiaRequests.php` |
| Vite | `vite.config.js` (plugin vue + resolver AntD + alias `@`) |
| Tema / helpers | `resources/js/theme.js`, `resources/js/lib/notify.js` |

Comandos:

```bash
npm run dev      # desarrollo con HMR
npm run build    # build de producción
php artisan serve
```

## Patrón de migración (repetir por cada pantalla)

### 1. Controlador: `view()` → `Inertia::render()`

```php
// Antes
return view('estudiante.progreso', compact('estudiante', 'asignaturas'));

// Después
return \Inertia\Inertia::render('Estudiante/Progreso', [
    'estudiante' => $estudiante,
    'asignaturas' => $asignaturas,
]);
```

- El primer argumento es la ruta del componente dentro de `resources/js/Pages/`.
- Los datos pasan como props. Evita mandar modelos Eloquent crudos con campos
  sensibles; arma arrays explícitos como en `HandleInertiaRequests`.
- Endpoints que ya devuelven `response()->json()` para `fetch()` **no se tocan**:
  se siguen consumiendo con `axios` desde el componente, o se convierten a props.

### 2. Vista Blade → componente `.vue`

- `resources/views/estudiante/progreso.blade.php` → `resources/js/Pages/Estudiante/Progreso.vue`
- Reemplazar el HTML+CSS a mano por componentes Ant Design (`a-card`, `a-table`, `a-form`,
  `a-statistic`, `a-tabs`, `a-list`, `a-tag`, `a-modal`…).
- El `<script>` inline (fetch, listeners) se reescribe con `<script setup>` + `axios`.
- `@yield('content')` desaparece: el contenido va dentro de `<EstudianteLayout>` / `<AdminLayout>`.
- `{{ route('x') }}` → `route('x')` (Ziggy, global).
- `<a href>` interno → `<Link href>` de `@inertiajs/vue3`.
- Formularios → `useForm()` de Inertia + botón con `@click` (no `@finish`).
- Flash: lo muestran los layouts vía `showFlash(page.props.flash)`.
- Confirmaciones de borrado → `confirmDelete()` de `@/lib/notify`.

### 3. Rutas

Las rutas de `routes/web.php` no cambian de URL ni de nombre. Solo cambia lo que
el controlador devuelve. Ziggy expone todos los nombres de ruta al frontend.

### 4. Layouts

- [x] `Layouts/EstudianteLayout.vue`  (basado en `estudiante/layouts/app.blade.php`)
- [x] `Layouts/AdminLayout.vue`        (basado en `administrador/layouts/master.blade.php`; incluye polling de notificaciones de pagos)
- [x] `Layouts/PublicLayout.vue`       + `Components/AuthModal.vue` (login/registro/recuperación con `useForm`)

## Inventario de vistas (checklist)

Se quedan en Blade (no son UI de SPA): `emails/*`, `estudiante/reporte-pdf`,
`administrador/estudiantes/reporte-pdf`, `app.blade.php`.

### Público / Auth
- [x] `index.blade.php` → `Pages/Welcome.vue` (landing + `AuthModal`)
- [x] `terms.blade.php` → `Pages/Terms.vue`
- [x] `auth/reset-password.blade.php` → `Pages/Auth/ResetPassword.vue` (página propia con blobs,
      `ResetPasswordController@showResetForm` → `Inertia::render('Auth/ResetPassword', ['token','email'])`;
      `@reset` sigue devolviendo JSON y se consume con axios; estado de éxito in-page). Verificado.
- [x] `AuthController@login` / `@register` convertidos a respuestas Inertia (redirect / `withErrors`)
- [ ] `ForgotPasswordController` sigue devolviendo JSON (se consume con axios desde `AuthModal`)

### Panel Estudiante  (`resources/js/Pages/Estudiante/`)  — **COMPLETO + verificado en navegador**
- [x] `dashboard` → `Dashboard.vue` (stats vía APIs `api/estadisticas`, `api/ultimos-examenes`,
      `api/tiempo-estudio`, `recomendaciones`; heartbeat cada 60s; tarjetas de acceso rápido + meta universitaria)
- [x] `completar-perfil` → `CompletarPerfil.vue` (`useForm` → Inertia redirect; selects de prepa/universidad con búsqueda)
- [x] `perfil` → `Perfil.vue` (axios a los endpoints JSON `perfil.actualizar` / `perfil.cambiar-password` /
      `subir.foto` / `eliminar.foto`; `router.reload({only:['auth']})` tras cada cambio)
- ~~`progreso`~~ **RETIRADO** por decisión del cliente ("no tiene caso"): su contenido (stats, meta
  universitaria, historial) ya vive en el dashboard y en "Mis exámenes". `Progreso.vue` eliminado;
  `AlumnoController::progreso()` ahora `redirect()->route('estudiante.dashboard')` (ruta conservada por
  compatibilidad); quitado del menú de `EstudianteLayout` y de las tarjetas de acceso del dashboard.
- [x] `clases-premium` → `ClasesPremium.vue` (collapse por asignatura, modal con iframe embed
      YouTube/Vimeo/Drive, `registrar.progreso.video`, enlace a examen de materia; gate si no es premium)
- [x] `examenes` → `Examenes.vue` (tabla filtrable + búsqueda; consume `historial-examenes` + `api/estadisticas`)
- [x] `examen-materia` → `ExamenMateria.vue` (wrapper de `QuizRunner`)
- [x] `examen-curso` → `ExamenCurso.vue` (wrapper de `QuizRunner`)
- [x] `simulador` → `Simulador.vue` (wrapper de `QuizRunner` + selector de simulador + modo básico/límite)
- [x] `resultados` → `Resultados.vue` (círculo de calificación, timeline de intentos, acordeón de revisión)
- [x] `checkout` → `Checkout.vue` (cupón vía Inertia redirect + flash; transferencia/oxxo → `procesar-solicitud-pago`;
      Mercado Pago → axios `pago.mercadopago.crear` → `window.location = init_point`)
- [x] `checkout-pendiente` → `CheckoutPendiente.vue` (**vista que faltaba, ahora creada**)
- [x] `ficha-pago` → `FichaPago.vue` (datos + imagen ficha + descarga + `ComprobanteUpload`)
- [x] `pago-exito` → `PagoExito.vue` (`a-result`)
- [x] `mis-pagos` → `MisPagos.vue` (estado del último pago + `ComprobanteUpload` si pendiente/rechazado)
- [ ] `ver-respuestas` — método `verRespuestasExamen` existe pero **no está ruteado**; `resultados` ya cubre esto
- [ ] `instrucciones*` (parciales de pago) — no ruteados, se omiten

**Componentes nuevos**: `Components/QuizRunner.vue` (motor de examen compartido: timer, persistencia
en localStorage, navegación, límite de plan básico, POST axios → `router.visit(redirect)`,
`<Transition name="q">` entre preguntas),
`Components/ComprobanteUpload.vue` (botón + modal `a-upload-dragger` → `subir-comprobante`).

**Pase de diseño del panel de estudiante** (2ª iteración): `resources/css/app.css` ahora tiene una capa
de animaciones (`sains-rise/fade/pop/float/blob`, `.sains-stagger` para entrada escalonada de grids,
`prefers-reduced-motion`), un componente `.sains-hero` reutilizable (degradado + blobs animados +
anillo de progreso conic-gradient con `--v`), y pulido de `.ant-card` (hover-lift), `.ant-collapse`
(tarjetas redondeadas) y `.ant-list-item`. `EstudianteLayout.vue` rehecho: fondo con blobs fijos,
header con nav propia (subrayado animado en el activo), botón "Hazte Premium", item "Clases" en el menú.
`Dashboard`, `Progreso`, `Examenes`, `Checkout`, `Perfil`, `CompletarPerfil`, `MisPagos`, `FichaPago`,
`CheckoutPendiente` usan `.sains-hero` en vez de `PageHead` (el componente `PageHead.vue` ya no lo usa
ninguna página de estudiante). `Resultados` tiene su propio `.result-hero` verde/rojo según aprobado.
`ClasesPremium` rediseñado con hero + tarjetas de clase con miniatura de video (vumbnail/ytimg) +
progreso por asignatura + estado "vista". `Simulador` / `ExamenMateria` / `ExamenCurso` sin `PageHead`
(QuizRunner ya trae su cabecera; los de examen tienen una barra `Clases ←` + tag "Mejor: N%").

**Cambios de backend** (`AlumnoController`): todos los `view()` de estudiante → `Inertia::render()` con
payloads explícitos; helpers privados `datosPago()` / `datosExamen()` / `formatearPreguntasQuiz()`
(baraja opciones y las etiqueta `correcta`/`incorrecta1`…); `completarPerfil` y `aplicarCupon`/`eliminarCupon`
ahora devuelven redirects con flash (`success`/`error`, ya no `*_cupon`); `simuladorBasico` con límite
alcanzado ahora renderiza la página con `limiteAlcanzado:true` en vez de un redirect en bucle.
Ruta `/estudiante/examenes` (closure) → `Inertia::render('Estudiante/Examenes')`.
GOTCHA encontrado: `.ant-radio-group` trae `font-size:0` (truco antd) — los `<label>` propios dentro
de un `<a-radio-group>` heredan 0px; hay que re-fijar `font-size` en el contenedor.

**Pulido extra (3ª iteración)**: sin emojis en ninguna .vue (petición del cliente). QuizRunner:
opciones con letra A/B/C + check al seleccionar, header con blob animado. Simulador: selector de
simulador como tarjetas ("Simulador 1/2/3" si los nombres colisionan) con nº de preguntas y minutos.
PagoExito rediseñado (hero naranja/verde + pasos "¿qué sigue?"). Perfil: tarjeta de avatar con
portada degradada y avatar montado sobre ella.

**Pulido de tablas + layouts (5ª iteración)**: se quitó la etiqueta "ADMIN"/"ESTUDIANTE" junto al
logo en ambos layouts. Header (ambos): línea de degradado animada arriba, blur más marcado, sombra
índigo tenue, ítem de menú activo con pastilla índigo + subrayado morado, chip de usuario con
degradado y sombra. **Tablas** (`app.css`, aplica a todo): encabezado con degradado índigo tenue +
texto índigo en mayúsculas, filas cebra (`nth-child(even)`), hover con tinte índigo + barra de acento
lateral con degradado, `<a-tag>` dentro de tabla como pastillas sin borde, página activa de la
paginación en índigo sólido, contenedor con borde + radio 14px.

**6ª iteración — AdminLayout + acciones + modales**:
- `AdminLayout` rehecho con la MISMA estructura que `EstudianteLayout` (divs planos en vez de
  `<a-layout>`, fondo de blobs fijos, nav propia). La nav horizontal ahora son botones `.adm-nav__item`
  (los grupos Evaluación/Financiero/Instituciones/Catálogos son `<a-dropdown trigger="hover">` con
  overlay `<a-menu class="adm-submenu">` estilizado). "Inicio" ahora vive en la nav (antes solo en el drawer).
- **`Components/RowActions.vue`** nuevo: grupo de botones de acción (ver/editar/eliminar) como pastilla
  blanca con hover por color (ver=azul, editar=índigo, eliminar=rojo), tooltips, `#extra` para botones
  propios (p. ej. "Duplicar" en Exámenes), props `editable`/`edit-href`/`view-href`/`delete-disabled`.
  Aplicado a las 14 páginas de índice del admin.
- **Diálogos de confirmación** (`lib/notify.js`): `confirmDelete`/`confirmAction` ahora renderizan una
  cabecera ilustrada (icono en cuadro de color redondeado + título) + descripción + botones redondeados
  a ancho completo. `confirmAction` acepta `danger` y `tone` (`delete|warn|ask|logout`). Estilos en
  `app.css` bajo `.sains-confirm`.
- **Filtros añadidos**: Clases → filtro "Recursos" (con video / con material / sin recursos) +
  `ClaseController` lo soporta; Universidades → filtro "Tipo" (Pública/Privada/Autónoma) +
  `UniversidadController` lo soporta.

**7ª iteración — lógica + más color**:
- **Clases gratuitas**: migración `add_gratis_to_clases_table` (columna `gratis` bool). `Clase` con
  cast. `ClaseController` (filtro `acceso`, store/update con `$request->boolean('gratis')`, stat
  `gratuitas`). Index admin: columna+filtro "Acceso" + switch en el modal. `AlumnoController::clasesPremium`
  pasa `gratis` por clase; `Estudiante/ClasesPremium.vue` usa `puedeVer(c) = premium || c.gratis`
  (badge "Gratis" + candado solo si de verdad bloqueada).
- **Videos**: `VideoController` stats corregidas (`premium` / `publicos`). `Admin/Videos/Index.vue`
  rediseñado como **grid de tarjetas** con miniatura de video, badge Premium/Público, duración,
  filtro `a-segmented`.
- **Call Center**: `InteraccionCallCenterController::index` reescrito para **agrupar por estudiante**
  (paginación por estudiante, `grupos` con `interacciones[]`). `CallCenter/Index.vue`: tabla expandible
  (fila = estudiante, expandida = timeline de interacciones) + botón **"Retomar"** que abre el modal
  con `id_estudiante` precargado + fecha/hora de hoy. GOTCHA: los métodos usaban `$request->ajax()`
  (true en Inertia por `X-Requested-With`) → devolvían JSON y rompían Inertia; cambiados a
  `$request->wantsJson()` (todo el archivo, vía sed).
- **Botones de acción** (`RowActions.vue`): iconos con **color de reposo** por acción (ver=azul,
  editar=índigo, eliminar=rojo, copiar=ámbar) — ya no sólo en hover; se quitó la pastilla contenedora.
- **Ver comprobantes**: `PagoController::index` añade `comprobante_url` / `comprobante_es_pdf`.
  `Pagos/Index.vue`: botón extra en `RowActions` que abre un modal con la imagen del comprobante
  (o enlace si es PDF). `Pagos/Show.vue`: banner de monto verde/rojo/ámbar según estatus + tarjeta
  de comprobante con manejo de PDF.
- **Show con más color**: `Estudiantes/Show` (tarjeta de ficha con avatar degradado + `StatCard`s —
  faltaba `import StatCard`), `ExamenesRealizados/Show` (banner de calificación verde/rojo),
  `Pagos/Show` (banner de monto). Dashboard admin: barras de progreso en Top 5, iconos de color en
  títulos de sección, tarjetas principales clicables.
- **Filtro añadido**: Carreras → "Calif. mínima" (con/sin) + `CarreraController`.

**Pulido del panel ADMIN (4ª iteración)**: `AdminLayout` con fondo de blobs fijos + header con
blur más marcado + estado activo del menú horizontal resaltado. `Admin/Dashboard` con `.sains-hero`
(saludo + 3 cifras destacadas) en vez de `PageHead`. `app.css`: `.sains-stats` ahora SIEMPRE anima
la entrada escalonada de sus tarjetas (no hace falta la clase `.sains-stagger`); `.sains-pagehead`
con animación de entrada + brillo (shimmer) en el icono; `.ant-page-header` con botón de "atrás"
estilizado (badge índigo). Todas las páginas admin que usaban `<a-page-header>` migradas a `PageHead`
(Estudiantes/Examenes/Pagos Create+Edit+Show, ExamenesRealizados/Show, Admin/Perfil, los dos
sub-dashboards de Pagos y Exámenes); sus `<a-statistic>` sueltos pasaron a `StatCard`.

### Panel Admin  (`resources/js/Pages/Admin/`)
- [x] `dashboard` (1023) → `Pages/Admin/Dashboard.vue` (`AdminController@dashboard` devuelve Inertia con payload limpio)
- [x] `perfil` → `Pages/Admin/Perfil.vue` (datos + cambio de contraseña; `updatePerfil` ahora también actualiza el registro `Administrador`)
- [x] estudiantes: index/create/edit/show → `Pages/Admin/Estudiantes/*` (páginas separadas + `EstudianteForm.vue`; selects de prepa/universidad con búsqueda de toda la lista, sin cascada; `Show` con stats resumidas + gráfico semanal + reset de contraseña en modal). Fix `plan_activo` con `$request->boolean`.
- [x] preparatorias: index/create/edit → `Pages/Admin/Preparatorias/*` (**CRUD de referencia**: `PreparatoriaForm.vue` compartido, `Components/Pagination.vue`, búsqueda con debounce vía `router.get`, borrado con confirm). `store`/`update` normalizan columnas NOT NULL. Falta: filtro dinámico de municipios (`getMunicipios` API) y la vista `show` (no existía).
- [x] universidades → `Pages/Admin/Universidades/Index.vue` (modal CRUD)
- [x] administradores → `Pages/Admin/Administradores/Index.vue` (modal CRUD; crea User+Administrador, no permite auto-borrado)
- [x] preguntas → `Pages/Admin/Preguntas/Index.vue` (modal CRUD)
- [x] cupones → `Pages/Admin/Cupones/Index.vue` (modal CRUD + modal "generar masivo" + regenerar código vía axios)
- [x] pagos → `Pages/Admin/Pagos/*` (Index+Show+Create+Edit+Dashboard+PagoForm). Show con aprobar/rechazar (modal de motivo). Edit sube comprobante (`useForm` + `_method:put` + `<input type=file>`). Fix bug `->usuario->` en `PagoController` (usar `getRelation`).
- [x] examenes → `Pages/Admin/Examenes/*` (Index+Create+Edit+Show+Dashboard+ExamenForm). Selector de preguntas con `a-table` + `row-selection` (filtro por área); `numero_preguntas` se sincroniza con la selección. Modal "generar automático" en el Index.
- [x] examenes_realizados → `Pages/Admin/ExamenesRealizados/{Index,Show}.vue` (solo lectura + borrado; Show decodifica el JSON de respuestas).
- [x] interacciones_call_center → `Pages/Admin/CallCenter/Index.vue` (modal CRUD; `a-time-picker`/`a-date-picker`; coalesce `nota`/`resultado` NOT NULL). `AdminController::callcenter()` sigue muerto (no ruteado).
- [x] asignaturas → `Pages/Admin/Asignaturas/Index.vue` (modal CRUD, campo único)
- [x] carreras → `Pages/Admin/Carreras/Index.vue` (modal CRUD; `withCount('universidades')`, JSON→redirect)
- [x] videos → `Pages/Admin/Videos/Index.vue` (modal CRUD; fix `plan` con `$request->boolean`, `duracion` default `00:00:00`)
- [x] clases → `Pages/Admin/Clases/Index.vue` (modal CRUD campos base; los "recursos adicionales" se conservan pero no se editan aún)

**Patrón usado para estos CRUDs**: una sola `Index.vue` con `<a-table class="filtered-table">`
(paginación server-side mapeada + `@change`), y un `<a-modal>` con `useForm` para crear/editar
(mismo formulario, `editing.value` distingue). `create`/`edit`/`show` de los controladores ahora
solo hacen `redirect()->route('...index')`. `store`/`update`/`destroy` devuelven redirects con flash.

**Filtros dentro del encabezado**: los controles de búsqueda/filtro van en el slot `#headerCell`
de la `<a-table>`, cada uno debajo del título de su columna y alineado con ella
(`<div class="th"><span class="th-title">{{ title }}</span> <a-input/a-select size="small" .../></div>`).
Los estilos están en `resources/css/app.css` bajo `.filtered-table`. El estado de los filtros son
`ref`s con `watch` + debounce que llaman `router.get(..., { preserveState: true, replace: true })`.

Los `partials/table_rows.blade.php` desaparecen: pasan a ser un `v-for` dentro
del componente index correspondiente.

## Orden sugerido

1. ~~`PublicLayout` + `index` (login/registro)~~ ✅
2. ~~`AdminLayout` (Ant Design)~~ ✅
3. ~~CRUD de referencia (`preparatorias`)~~ ✅
4. ~~Resto de CRUDs catálogo admin: universidades, administradores, asignaturas, carreras, videos, clases, cupones, preguntas~~ ✅
5. ~~`perfil`, `estudiantes`~~ ✅
6. ~~`pagos`, `examenes`, `examenes_realizados`, `interacciones_call_center`~~ ✅
   → **EL PANEL ADMIN ESTÁ COMPLETO.** No quedan vistas Blade de admin reachables.
7. ~~Panel de estudiante completo~~ ✅ → **EL PANEL DE ESTUDIANTE ESTÁ COMPLETO** (verificado en navegador
   con `prueba@prueba.com`: login → dashboard → progreso → simulador (3 preguntas) → enviar → resultados →
   mis exámenes → clases premium → perfil (guardar) → checkout (cupón)).

## Iteración 22 (navbar en auth, navbar landing con más vida, animaciones, checkbox) ✅

- **Navbar en login / registro / recuperar**: `AuthShell.vue` ahora tiene un `<header class="au-nav">`
  con el logo (Link a `/`) a la izquierda y a la derecha un enlace "Inicio" + slot `#nav`
  (pastilla degradada azul); Login → "Crear cuenta", Register/Forgot/Reset → "Iniciar sesión".
  Los enlaces del nav van en oscuro sobre el lado blanco y en blanco cuando el panel se apila
  (<1024 px). Se quitó el logo duplicado del panel azul (el del nav lo cubre).
- **Animaciones en las pantallas de auth**: entrada escalonada de cada campo del formulario
  (`.au-card :deep(.au-form) > *:nth-child(n)` con delays), panel de marca que desliza desde la
  izquierda, features que suben una tras otra, arco dorado y anillo flotantes (`au-float`),
  subrayado dorado tras "tu lugar", tarjeta que entra con fade+scale. Guard de
  `prefers-reduced-motion`.
- **Navbar del landing** (`PublicLayout.vue`) con más color y animación: línea de acento con
  degradado animado (aparece al hacer scroll), fondo glass más marcado, marca con hover
  (escala + leve rotación), enlaces con subrayado degradado que crece desde la izquierda,
  **hamburguesa animada** (3 barras → X), menú móvil con fade/slide (opacity + pointer-events),
  botón "Iniciar sesión" fantasma (borde) + nuevo botón "Crear cuenta" con degradado y brillo
  animado (`pub-shine`).
- **Checkbox de términos alineado**: en `Register.vue` se reemplazó el `<a-checkbox>` con label
  interno por un `<div class="au-terms">` (flex, `align-items:center`) con el checkbox + un
  `<span>` clicable aparte; el enlace de Términos lleva `@click.stop`.
- **Precio del landing**: el `$2,499` que se veía era del **build viejo**; el fuente ya decía
  `$800` (con `$1,200` tachado). Un rebuild lo dejó correcto — verificado ($800 MXN, "Pago único ·
  acceso por 1 año"). No hay ningún `2,499` en el código.
- Verificado en :8002 (8000=CLUB, 8001=RIC): navbar en auth, registro E2E → completar-perfil,
  checkbox alineado, navbar landing con sus dos botones, precio $800, sin errores de consola.

## Iteración 30 (upgrade Laravel 10 → 12) ✅

Rama `upgrade/laravel-12` (no mezclada a `main` todavía — probar y hacer merge cuando el
usuario confirme). PHP local es 8.2.29; Laravel 13 pide PHP ^8.3, así que el objetivo real
fue **Laravel 12** (última compatible con el PHP instalado en WAMP, compartido con los demás
proyectos — subir PHP es una decisión aparte, más disruptiva).

- `composer.json`: `php ^8.2`, `laravel/framework ^12.0`, `laravel/sanctum ^4.0`,
  `nunomaduro/collision ^8.1`, `phpunit/phpunit ^11.0`. `inertiajs/inertia-laravel`,
  `barryvdh/laravel-dompdf`, `laravel/socialite`, `tightenco/ziggy` ya soportaban Laravel 12
  con su constraint actual — no se tocaron. `composer update` resolvió todo sin conflictos.
- **Se conservó la estructura clásica** (`app/Http/Kernel.php`, `Console/Kernel.php`,
  `Exceptions/Handler.php`) — la guía oficial de Laravel 11/12 dice explícitamente que NO
  hace falta migrar a `bootstrap/app.php` en apps existentes, sigue soportada tal cual.
- `config/sanctum.php`: llaves de middleware renombradas para Sanctum 4
  (`authenticate_session`/`validate_csrf_token`). Sanctum no se usa de verdad en la app (auth
  100% por sesión custom) — cambio solo de forma, cero riesgo funcional.
- **Bug real encontrado y corregido** (`User.php`): faltaba `$authPasswordName = 'contraseña'`.
  Laravel 11+ intenta re-hashear y GUARDAR la contraseña en la columna `password` en cada
  login si cambió el work factor del hash; la tabla `usuario` no tiene esa columna (es
  `contraseña`) → sin el fix, un rehash habría roto el login con un error SQL de columna
  inexistente.
- **Bug real encontrado y corregido** (Carbon 3, incluido en L11+): invierte el default de
  `diffIn*()` de absoluto a con signo. Se envolvieron en `abs()` los 3 `diffInMinutes()` que
  dependían de la magnitud: `ResetPasswordController` (expiración del token de recuperación —
  **sin el fix, los enlaces de "recuperar contraseña" nunca habrían expirado**),
  `SessionTimeout` y `TiempoEstudio`.
- `phpunit.xml` migrado al nuevo esquema (`phpunit --migrate-configuration`).
- Verificado en :8002: login admin/estudiante, dashboard, Ver pago, Show de estudiante, alta
  de cupón (POST con CSRF real), validación de referencia de pago duplicada — todo sin
  errores nuevos en `storage/logs/laravel.log`. Suite de tests (2) en verde.
- **Pendiente**: probar a fondo en un ambiente aparte antes de mergear a `main` y desplegar;
  en producción hay que correr `composer install` + `npm run build` tras el deploy.

## Iteración 29 (Show del estudiante: quitar pagos, mejorar progreso y exámenes) ✅

- **`Admin/Estudiantes/Show.vue`**: se quitó la sección **Pagos** (tabla + `pagoColumns` + `money`);
  `EstudianteController@show` ya no envía `pagos` al render (prop declarada pero no usada).
  Queda: cabecera de perfil + 4 KPI + Progreso de videos + Estudio 7 días + Exámenes realizados
  (esta última **a todo el ancho** al liberar la columna de pagos).
- **Progreso de videos** rediseñado: círculo + **barra segmentada** (completados indigo / en
  progreso ámbar / sin ver gris, `videosSinVer = total - completos - en_progreso`) + leyenda con
  puntos de color y conteos.
- **Exámenes realizados** rediseñado: meta en el título (`N en total · promedio X/100`); columna
  Tipo como pill de color (`tipoMeta`: materia azul / curso verde / simulación rojo / otro
  violeta); Calificación como **barra de score** con color por rango (≥80 verde, ≥60 ámbar, <60
  rojo) + número; estado vacío propio (`FileDoneOutlined`); `:scroll="{ x: 460 }"` para móvil.
- Avatar: `@error="fotoOk = false"` → si la foto (`foto_url`) da 404, cae a iniciales.
- Verificado en :8002 (desktop + móvil): sin errores propios (los 404 de `/storage/fotos_perfil`
  son archivos que faltan en local y ya caen a iniciales).

## Iteración 28 (rediseño: Ver pago, modal Compartir cupón, Show del estudiante) ✅

- **`Admin/Pagos/Show.vue`** rehecho: banner de estado (verde/rojo/ámbar) con monto + chips;
  layout de **2 columnas flex independientes** (`.pay__cols`, sin `align-items:stretch` que dejaba
  huecos): izquierda = "Detalle del pago" (grilla clave/valor `.kv` con divisores finos, ya no
  `a-descriptions bordered`) + "Comprobante" (imagen grande / bloque PDF / vacío); derecha =
  "Estudiante" (avatar + correo + plan + botón) + "Nota del pago". Cada columna apila sus
  tarjetas a su altura natural → sin espacios muertos.
- **`Admin/Cupones/Index.vue` — modal Compartir** rediseñado: hero degradado con **logo SAINS
  en blanco (`<img>`, SIN link)** + orbe ámbar; **voucher** tipo cupón (código mono + "Válido
  hasta" + bloque degradado "X% de descuento" con corte punteado); campos WhatsApp/correo;
  preview del mensaje; botón WhatsApp full + fila correo/copiar. `.share-modal` con
  `border-radius:20px`.
- **`Admin/Estudiantes/Show.vue`** rehecho como **bento** (`.bento`, `gap:14px`): cabecera de
  perfil horizontal (avatar + nombre + plan + **facts en `flex-wrap` que llenan cada fila sin
  celdas vacías**); fila de 4 StatCard; fila `1fr 1fr` Progreso (círculo + leyenda) / Estudio 7
  días (barras, con estado vacío propio si no hay sesiones); fila `1fr 1fr` Exámenes / Pagos.
  Todas las tarjetas `.card` con el mismo borde/radio/padding, columnas `align-items:stretch`.
  Responsive: perfil apila en ≤640px.
- Verificado en :8002 (admin): las 3 vistas sin errores de consola, sin huecos, responsive OK.

## Iteración 27 (fix referencia de pago duplicada + limpieza SweetAlert) ✅

- **Bug** (producción): al registrar un pago desde el admin →
  `SQLSTATE[23000] 1062 Duplicate entry 'TRA…' for key 'pagos_referencia_pago_unique'`.
  Causa: `pagos.referencia_pago` es `UNIQUE NOT NULL` y `PagoController::store` insertaba
  `$request->referencia_pago` tal cual; el admin tecleaba una referencia que ya existía (la de la
  ficha pendiente del estudiante). Además `AlumnoController::generarReferenciaPago` usaba
  `rand(100,999)` (colisiona fácil).
- **Fix**:
  - `App\Models\Pago::referenciaUnica($prefix)` — genera una referencia y **reintenta** hasta que
    no exista. `Pago::referenciaPreferida($preferida, $prefixFallback)` — usa la preferida si está
    libre, si no genera una única.
  - `PagoController::store` / `update`: regla `unique:pagos,referencia_pago` (con
    `,{id}` en update) + mensaje claro *"Ya existe un pago con esa referencia…"* (se muestra bajo
    el campo en `PagoForm.vue`). Si la referencia viene vacía → `Pago::referenciaUnica()`.
    En update, si viene vacía → conserva la actual (columna NOT NULL).
  - `AlumnoController::generarReferenciaPago`: bucle `do…while` hasta referencia libre.
  - Spots de cupón 100% (`AlumnoController::activarPlanPorCupon`,
    `EstudianteController@store`): `Pago::referenciaPreferida('CUPON-'.$codigo, 'CUPON')`.
  - Verificado con tinker: referencia duplicada → `ValidationException` (redirige atrás, **sin
    500**, no crea el pago); referencia vacía → autogenera `TRA20260909…`; estudiante y cupón OK.
- **SweetAlert**: **no había ninguno en el código** — la migración ya lo reemplazó por
  Ant Design Vue (`lib/notify.js` → `Modal.confirm` + `message`, `a-popconfirm` en tablas).
  Sólo quedaba `sweetalert2` como dependencia sin usar en `package.json` → **eliminada**
  (`npm uninstall sweetalert2`, build OK). Lo que se ve en producción es el build viejo;
  al desplegar el build actual todas las alertas ya son de Ant Design Vue.

## Iteración 26 (rediseño del landing) ✅

Reescritura completa de `resources/js/Pages/Welcome.vue` (template + `<style scoped>`) con un
sistema visual coherente (indigo `#4f46e5`→violeta `#7c3aed`, acento ámbar, radios grandes,
sombras en capas). Sin dependencias nuevas — sigue con grid de Bootstrap + Font Awesome + CSS scoped.

- **Revelado al hacer scroll**: directiva local `v-reveal` con `IntersectionObserver`
  (`.reveal` → `.is-in`). Directiva revela al instante lo que ya esté en pantalla/por encima al
  montar (deep-link con `#ancla`); listener de `scroll` (rAF-throttled) + `setTimeout(2500)` de
  respaldo barren lo que el observer no haya cazado; se limpian en `onBeforeUnmount`.
  Guard `prefers-reduced-motion`.
- **Hero** (`#inicio`): degradado profundo + orbes difuminados que flotan + patrón de puntos con
  máscara + glow. Badge con punto verde pulsante, título con "universidad de tus sueños" en
  degradado ámbar, dos CTAs (sólido blanco + fantasma → hace scroll a `#metodo`), **tira de stats**
  en tarjeta glass (`+85%`, `150+`, `5,000+`, `8`), tira de confianza con las 6 universidades como
  pills, flecha de scroll animada. Entrada escalonada de cada bloque (`hero-in`).
- **Secciones**: patrón `.sec__head` (eyebrow con degradado + `sec__title` + `sec__sub`).
  Beneficios (3 tarjetas con icono en tile degradado), banner de admisión rediseñado (degradado +
  forma decorativa + logos de universidades en chips blancos), **método como stepper** de 4 pasos
  numerados + panel con imagen enmarcada (las `*_tips.png` son 200×200, se muestran a máx 190px),
  docentes con foto `aspect-ratio 1/1` + overlay degradado + zoom en hover + área como pill,
  Plan Premium pulido (tag, precio grande rosa, lista de 8 features en 2 columnas, nota de pago
  seguro) + tarjeta de garantía con icono verde, y **nueva franja CTA final** ("¿Listo para
  asegurar tu lugar?").
- `heroStats`, `beneficios`, `premiumFeatures` como arrays en el `<script setup>`.
- Verificado en :8002: estructura completa (5 sections + footer#contacto, 3 beneficios, 4 pasos,
  8 docentes, 8 features), todos los estilos nuevos aplicados (computed styles), sin errores de
  consola, sin imágenes rotas, build OK. El scroll del Browser pane se congela — la verificación
  visual completa por scroll quedó bloqueada por el pane (el hero sí se vio y quedó bien).

## Iteración 25 (navbar del landing: scrollspy + animaciones) ✅

- **`Welcome.vue`**: el `<section class="hero">` ahora es `id="inicio"`.
- **`PublicNav.vue`** — la sección activa te va siguiendo mientras haces scroll:
  - `activeSection` (ref) se calcula en `computeSpy()` a partir de `scrollY + 130` vs el
    `getBoundingClientRect().top` de cada sección (`inicio/nosotros/metodo/docentes/plan/contacto`);
    al fondo del documento fuerza `contacto`. Se dispara con `scroll` (throttle `requestAnimationFrame`),
    `resize`, y un `setInterval(700 ms)` de respaldo (por si no hay evento scroll).
  - Al hacer clic en un enlace: fija `activeSection` al instante y bloquea el spy 900 ms
    (`spyLockUntil`) para que el scroll suave llegue sin "caminar" por las secciones intermedias.
  - `isActive(l) = enInicio && activeSection === l.section` (en otras páginas: nada activo).
  - **Indicador deslizante**: `<span class="pnav__indicator">` — pastilla con degradado que se
    mueve (`translate` + `width` con transición `.38s`) hasta el enlace activo. Se recoloca en
    `watch([activeSection, enInicio])`, `resize`, `document.fonts.ready` y a los 420 ms.
- **Animaciones nuevas**: entrada del navbar (`pnav-drop`, baja deslizando), barra de acento
  superior con degradado que fluye (`pnav-flow`), entrada escalonada de los enlaces
  (`pnav-link-in` con `--i`), "pop" del icono al activarse (`pnav-pop`), sombra al hacer scroll
  (`.is-scrolled`), menú móvil con enlaces escalonados y activo con degradado + borde izquierdo.
  Guardado con `@media (prefers-reduced-motion: reduce)`.
- Verificado en :8002: el indicador sigue a la sección correcta en las 6 secciones
  (`nosotros`→123px, `metodo`→232px, `docentes`→332px, `plan`→444px, `contacto`→584px,
  `inicio`→37px), feedback inmediato al hacer clic, en `/login` sin activo ni indicador,
  sin errores de consola. (El scroll programático del Browser pane se congela — artefacto del
  pane; en un navegador real el scroll suave del clic funciona.)

## Iteración 24 (fix: el login abría el dashboard en un modal raro) ✅

- **Síntoma** (prod `sains.sistema-sicad.site`, admin y estudiante): al iniciar sesión, el
  dashboard aparecía dentro de un overlay a pantalla completa y la URL seguía en `/login`.
- **Causa**: es el **modal de error interno de Inertia** (`@inertiajs/core` → `modal.show()`),
  que salta cuando una petición XHR recibe una respuesta que **no** es Inertia. Disparadores:
  1. En producción, la redirección tras login se genera como `http://` (por `APP_URL` en http
     y/o proxy no confiable). El navegador, al seguir el redirect `http→https`, **descarta la
     cabecera `X-Inertia`**, así que el GET del dashboard responde el HTML completo → modal.
  2. Token CSRF vencido → `POST /login` devuelve **419 Page Expired** → modal con "419"
     (reproducido en local con cookie `XSRF-TOKEN` vieja / sesiones de archivo borradas).
- **Fixes**:
  - `app/Http/Middleware/TrustProxies.php`: `protected $proxies = '*'` — Laravel ahora
    honra `X-Forwarded-Proto: https` y genera URLs `https://`.
  - `app/Providers/AppServiceProvider.php` `boot()`: si `config('app.url')` empieza con
    `https://`, `URL::forceScheme('https')`.
  - `resources/js/app.js`: `router.on('invalid', …)` como red de seguridad — un **419** recarga
    la página (token nuevo); una respuesta **2xx/3xx no-Inertia** hace `window.location.assign()`
    a la URL final (navegación real) en vez de mostrar el modal. No toca los **422** de
    validación (esos sí son respuestas Inertia válidas → siguen mostrando el error inline).
  - `.env.example`: comentario sobre `APP_URL` en producción.
- **Pendiente en el servidor** (hazlo tú): en el `.env` de producción →
  `APP_URL=https://sains.sistema-sicad.site`, `ASSET_URL=https://sains.sistema-sicad.site`,
  `SESSION_SECURE_COOKIE=true`; después `php artisan optimize:clear` y `npm run build`.
- Verificado en :8002: login admin → `/administrador/dashboard`, login estudiante →
  `/estudiante/clases-premium`, contraseña incorrecta → error inline (sin modal), y el caso
  419 → recarga automática en vez de modal. Sin overlay en ningún caso.

## Iteración 23 (navbar único estilo RIC en todas las páginas públicas) ✅

- **`Components/PublicNav.vue`** (NUEVO): un solo navbar para **todo** el público — landing,
  términos, login, registro, recuperar y restablecer. Estilo tomado de RIC (`AppLayout.vue`):
  barra `sticky` blanca glass (`rgba(255,255,255,.85)` + `backdrop-filter: blur(14px)`), 64 px,
  logo con subrayado degradado que crece en hover, enlaces pill con icono SVG + punto azul en
  el activo, dropdown de usuario (Ant `a-dropdown`, overlay `.pnav-menu` con cabecera degradada
  → Mi curso / Mi perfil / Cerrar sesión), o si es invitado los botones **Iniciar sesión**
  (fantasma) + **Crear cuenta** (degradado con brillo `pnav-shine`). Hamburguesa 3→X + menú
  móvil con fade/slide; en ≤900 px los CTA inline se ocultan y viven en el menú.
- **`PublicLayout.vue`**: se borró el `<nav>` inline + su CSS; ahora sólo `<PublicNav />`.
  Imports muertos limpiados (`onUnmounted`, `ref`, `Link`… quedó `computed/onMounted/watch`).
- **`AuthShell.vue`**: se quitó el `<header class="au-nav">` de la iteración 22 y sus overrides;
  se monta `<PublicNav />` arriba. `.au` → `min-height: calc(100vh - 64px)`, se quitaron los
  `padding-top` de 96 px. Login/Register/Forgot/Reset: se borró el `<template #nav>`.
- **Scroll a secciones del landing** (`/#nosotros`, `/#metodo`, …):
  - `PublicNav.go()`: si ya estás en el landing → `el.scrollIntoView({behavior:'smooth'})` +
    `history.replaceState`; si vienes de otra página → recarga completa (`window.location.href`).
  - `Welcome.vue onMounted`: lee `location.hash` y hace `scrollIntoView` (con `scroll-behavior`
    forzado a `auto` temporalmente) en reintentos [40, 200, 500, 900] ms para compensar el
    desfase de las imágenes que cargan después.
  - `app.css`: `:target, #nosotros, #metodo, #docentes, #plan, #contacto { scroll-margin-top: 88px }`
    para que la sección no quede bajo el navbar fijo.
- **Enlaces del navbar**: "Inicio" → `/` (o scroll-to-top si ya estás ahí); anclas → sección del
  landing; "Iniciar sesión"/"Crear cuenta" → `route('login')`/`route('registro')` vía `<Link>`.
- Verificado en :8002: navbar idéntico en `/`, `/login`, `/crear-cuenta`, `/recuperar-contrasena`
  y `/terminos-y-condiciones`; anclas cross-page aterrizan con la sección a 88 px del borde
  (`metodoTop`/`nosotrosTop` = 88); menú móvil (logo + hamburguesa) con 6 enlaces + 2 CTA;
  sin errores de consola. **Ojo dev**: el Browser pane sirve CSS/JS cacheado tras un rebuild —
  hace falta `location.reload(true)` para ver los cambios.

## Iteración 21 (login + registro: pantalla completa azul institucional) ✅

- **Login y registro pasan de modal a página completa** con diseño _split_ (panel de marca azul
  a la izquierda + tarjeta de formulario a la derecha), según la referencia del cliente.
- `Components/AuthShell.vue` — envoltorio compartido: panel azul con degradado, patrón de puntos,
  arco dorado, curva blanca de empalme, logo blanco, título "Asegura **tu lugar** en la universidad
  de tus sueños" y 3 features (Método comprobado / Docentes expertos / Plan Premium); a la derecha
  tarjeta blanca con slots `title` / `subtitle` / default / `google` / `footer`. Responsive: bajo
  1024 px se oculta el panel y queda la tarjeta sobre fondo azul.
- `theme.js` → nuevo `authTheme` (Ant Design en azul `#1d4ed8`, controles 44 px).
- Páginas nuevas: `Pages/Auth/Login.vue`, `Pages/Auth/Register.vue`, `Pages/Auth/ForgotPassword.vue`.
  `Pages/Auth/ResetPassword.vue` reescrita para usar `AuthShell`.
  - Login: `useForm().post(route('login'))`, alerta (`message.error`) con el error del backend,
    `message.success` al entrar. Enter y clic disparan `submit()` (`@click` en el botón +
    `@press-enter` en los inputs; el memory-gotcha de `<a-form @finish>` sigue vigente).
  - Register: medidor de fuerza de contraseña, checkbox de términos obligatorio.
- `routes/web.php`: `GET /login` (name `login`), `GET /crear-cuenta` (name `registro`),
  `GET /recuperar-contrasena` (name `password.request`), todas con middleware `guest`. Los `POST`
  siguen igual (`POST /login` sin nombre → `AuthController@login`; `POST /register` name `register`).
- `PublicLayout.vue`: `openAuth(v)` ahora hace `router.visit(route(v === 'register' ? 'registro' : 'login'))`
  en vez de abrir el modal; se quitó `<AuthModal>` y su estado. `Components/AuthModal.vue` eliminado.
- Verificado en navegador (server temporal en :8002, el 8000/8001 estaban ocupados por CLUB/RIC):
  login → dashboard, registro → completar-perfil, credenciales malas → toast, `guest` redirige a `/`.

## Iteración 20 (dropdown de perfil, anillo de %, botón examen, formularios, quitar WhatsApp) ✅

- **Botón flotante de WhatsApp del landing eliminado** (`PublicLayout.vue`: fuera `.pub-whatsapp`
  + su CSS). Los teléfonos de WhatsApp del footer se conservan (son datos de contacto).
- **Alertas en el login**: `AuthModal` — `onError` de login/registro muestra `message.error` con
  el primer error del backend (como el resto de la app); `onSuccess` muestra `message.success`.
- **Dropdown de perfil rediseñado** (ambos layouts): panel `.usr-menu` (en `app.css`) con cabecera
  (avatar + nombre + correo/plan sobre degradado; ámbar si es Premium), ítems con icono índigo,
  separador y "Cerrar sesión" en rojo; el pill de usuario gana estado `is-open` (borde + caret que
  rota). `goMenu()` cierra el menú al navegar.
- **`ClasesPremium.vue`**:
  - **Anillo de progreso con el % dentro** (SVG propio `.asig-head__ring`, verde al 100%),
    reemplaza el `a-progress` que sólo mostraba info al 100%.
  - **Botón "Examen de la materia"** rediseñado: pastilla con degradado ámbar→rosa y sombra
    (antes era `a-button type="text"` sin diseño).
  - Badge "Completada" en las miniaturas de clases vistas; subtítulo de asignatura muestra
    "N completadas".
- **Formulario de clases (registro/edición)**:
  - "Acceso" pasa de switch a **dos tarjetas seleccionables** (Premium / Gratuita) con icono.
  - Materiales adicionales: cada uno con **número**, cuerpo con título+tipo+enlace+descripción y
    botón de quitar; texto del botón cambia a "Agregar otro material".
- **Formularios en general**: los iconos de las etiquetas (`.sains-modal`, `.sains-form`) ahora
  van en un chip índigo; inputs del modal con hover lila + focus con anillo índigo.

## Iteración 19 (login/registro, exámenes, clases, avisos de tiempo, acordeón) ✅

- **`AuthModal.vue` rediseñado**: hero con degradado + brillo + lista de beneficios (en registro);
  **pestañas segmentadas** Iniciar sesión / Crear cuenta con indicador animado (reemplazan los
  enlaces de texto); medidor de fuerza de contraseña (4 barras + etiqueta); botón Google con
  estilo propio; nota de privacidad. Vista "forgot" aparte. Verificado.
- **`Estudiante/Examenes.vue` rediseñado**: hero con anillo de promedio; StatCards con iconos;
  tarjeta **"Tu evolución reciente"** (mini-gráfica de barras de los últimos 8 intentos, línea de
  aprobación 70%); tabla con icono ✓/✗ por fila, píldora de intento, calificación grande a color
  (verde/índigo/ámbar/rojo según nota), filtro `a-segmented`, empty state con CTA. Verificado.
- **`Estudiante/ClasesPremium.vue`**:
  - **Acordeón**: `<a-collapse accordion v-model:activeKey>` → al abrir una asignatura las demás
    se contraen; la primera abierta al entrar. Verificado.
  - Más diseño: paneles con borde redondeado + sombra al abrir, icono de asignatura que pasa a
    degradado índigo cuando está abierta, candado ámbar para estudiantes sin plan; tarjetas de
    clase más grandes con zoom de miniatura al hover, badges con sombra.
- **`QuizRunner.vue` — avisos de tiempo**: `message` de aviso (una sola vez) al llegar a la
  **mitad del tiempo**, a **1/4 restante**, a **5 min** y a **1 min**. El reloj cambia de color
  (índigo → ámbar en la mitad → rojo en los últimos 5 min) y su etiqueta ("Mitad del tiempo" /
  "¡Poco tiempo!"). Nueva **barra de progreso de tiempo** bajo el reloj. Verificado (reloj ámbar
  + "MITAD DEL TIEMPO" al cruzar el 50%).

## Iteración 18 (correos, PDF, pagos rápidos, cupón 100%, vista previa) ✅

- **Navbar admin arreglado** (regresión de la 17ª: `<a-tooltip>` + breakpoints ocultaban todo el
  menú en pantallas < 1360 px). Ahora `.adm-nav` es scroll horizontal sin barra, etiquetas
  siempre visibles, se compacta en pasos y sólo pasa a hamburguesa < 1040 px. Ítem activo =
  pastilla índigo rellena. Contraste subido (`#475569`).
- **`public/images/logo-sm.png`** (480×263, 20 KB) generado desde `logo.png` (7374×4034 → ~119 MB
  en memoria, reventaba dompdf). Se usa en los layouts y en el reporte PDF.
- **Correos rehechos** a prueba de clientes: `emails/layout.blade.php` (tablas, estilos en línea,
  sin flex/grid/@keyframes, acento por color). `pago-aprobado` (verde, "Ir a mis clases"),
  `pago-rechazado` (rojo, motivo + checklist + "Corregir y reenviar comprobante"),
  `pago-revertido` (ámbar). Verificados en navegador.
- **Reporte PDF rediseñado** (`administrador/estudiantes/reporte-pdf`): marca índigo, banda de
  cabecera, tarjetas de estadística con borde de color, títulos de sección con barra de acento,
  tablas cebra, badges de calificación. dompdf-safe. Verificado (PDF válido, 1.16 MB).
- **`.env`: `MAIL_MAILER` smtp → log** (mailpit no corre en WAMP; colgaba 3 s y registraba error
  en cada aprobación/rechazo). Para producción, poner SMTP real. `MAIL_FROM_ADDRESS` a
  `sains.ingreso@gmail.com`.
- **Pagos — acciones rápidas** (`Pagos/Index.vue`): botones ✓ Aprobar / ✗ Rechazar por fila en
  pagos pendientes/en revisión. Aprobar → `confirmAction` → `admin.pagos.aprobar` (activa plan +
  correo). Rechazar → modal con motivo + chips de motivos frecuentes → `admin.pagos.rechazar`
  (envía el correo de corrección). Alerta arriba cuando hay pendientes. Columna "Fecha y hora"
  (`fecha_pago` d/m/Y + hora de `fecha_pago`/`created_at`). Stats de pendientes incluyen
  `revisando`/`procesando`.
- **Botones de acción rediseñados** (`RowActions.vue`): agrupados en contenedor con borde,
  botones blancos 32 px con sombra, color pleno + elevación al hover; nuevas variantes
  `is-ok`/`is-no` para aprobar/rechazar.
- **Cupón que cubre el 100% → Premium automático** (sin pago): `Cupon::cubreTodo()` /
  `aplicable()`; `AlumnoController::activarPlanPorCupon()` (marca cupón usado, activa plan, crea
  `Pago` de $0 `tipo_pago='cupon'` `estatus='completado'`, notifica). Enganchado en
  `aplicarCupon`, `completarPerfil` y `admin/EstudianteController::store`. Verificado E2E.
- **Aprobar pago = activar curso**: ya ocurría en `store/update/aprobar/cambiarEstado`
  (`activarPlanEstudiante`). Sin cambios, confirmado.
- **Vista previa de 15 s en clases premium** (`ClasesPremium.vue`): un estudiante sin plan abre
  la clase, ve 15 s con banner de cuenta regresiva y luego un muro: "Paga la suscripción para
  tener acceso completo a los cursos" + botón "Hazte Premium". El `<iframe>` se destruye al
  expirar. Verificado en navegador.
- **Diseño**: `.sains-stat` con halo de color en la esquina + barra de acento en degradado +
  sombra en el icono; `.ant-card` con filo superior con brillo; cabeceras con elevación al scroll.

## Iteración 17 (formularios: validación, fechas, calendarios en español) ✅

- **Calendarios en español**: `app.js` importa `dayjs/locale/es-mx` + `dayjs.locale('es-mx')`;
  `theme.js` exporta `antdLocale` (es_ES) y los 3 layouts (`AdminLayout`, `EstudianteLayout`,
  `PublicLayout`) pasan `:locale="antdLocale"` al `<a-config-provider>`.
- **Componentes nuevos**:
  - `Components/PhoneInput.vue` — `<a-input>` que sólo admite dígitos (bloquea teclas y pega
    limpiando), `maxlength` 10 por defecto, `inputmode="numeric"`; normaliza el valor entrante.
  - `Components/DateField.vue` — `<a-date-picker>` formato `DD/MM/YYYY`, `disabled-date` según
    `limite`: `hoy` (sin futuras, default), `nacimiento` (ni futuras ni < 1920), `libre`.
  - `lib/forms.js` — `soloDigitos`, `maxHoy`, `fechaNacimientoValida`, `hoyISO`, `ahoraHM`,
    `esUrl`, `PRECIO_CURSO`.
- **Teléfonos** → `PhoneInput` en EstudianteForm, CompletarPerfil, Estudiante/Perfil, Admin/Perfil.
  Backend: `telefono` ahora `regex:/^[0-9]{10}$/` (celular) y `/^[0-9]{7,10}$/` (casa) en
  EstudianteController (store+update), AlumnoController (completarPerfil + actualizarPerfil),
  AdminController (3 formularios de admin).
- **Fechas** → `DateField` en los mismos formularios + PagoForm + CallCenter. Backend: `before:today`
  / `before_or_equal:today` en `fecha_nacimiento`, `fecha_pago`, `fecha_contacto`.
- **Call Center — hora no editable**: se quitó el `<a-time-picker>`; la hora se muestra en un input
  deshabilitado y se registra automáticamente (`ahoraHM()` al crear, se conserva al editar).
  `hora_contacto` pasó a `nullable` en el controller con fallback a `now()->format('H:i')`.
- **Pagos — monto fijo $800**: en `PagoForm` el monto es un input deshabilitado que muestra
  `$ 800.00` con nota. `PagoController::store/update` ya no validan ni leen `monto_pago` del
  request; usan `AlumnoController::PRECIO_CURSO`.
- **Comprobantes — vista previa antes de subir**:
  - `ComprobanteUpload.vue` (estudiante) reescrito: al elegir archivo, el dropzone se reemplaza
    por una vista previa (imagen o `<iframe>` para PDF) con botón "Cambiar"; el botón "Enviar"
    queda deshabilitado hasta elegir archivo. Usa `sains-modal`.
  - `PagoForm.vue` (admin): el `.file-drop` ahora muestra vista previa del comprobante seleccionado.
- **Clases — material adicional**: el modal expone la sección "Material adicional" (repetidor de
  `recursos`: título + tipo (`RecursoClase::TIPOS`) + URL + descripción, con agregar/quitar).
  `ClaseController::index` ahora envía `recursos` (detalle) y `tiposRecurso`; store/update ya
  soportaban `recursos[]` / `recursos_eliminar[]` (validación de `tipo` con `Rule::in` + `url`).
  Se quitó el input suelto "Material de apoyo" (el campo `clases.url` legacy se conserva al editar).
- **Modales**: `.sains-modal` con cabecera con brillo decorativo, iconos índigo en labels,
  cuerpo con `max-height`/scroll (+ `--wide`), scrollbar sutil, sombra en el botón primario.
- **Navbars**: cabeceras admin/estudiante ganan elevación al hacer scroll (`is-scrolled`) y
  tooltips en los ítems (útiles cuando el texto se oculta en pantallas medianas).

## Iteración 16 (revisión final del backend) ✅

Barrido completo del backend (`php -l` en todo `app/`, `routes/`, `config/`, `database/`;
`route:list`; `migrate:status`; inspección de esquema con tinker).

- **BUG corregido — `pagos.tipo_pago` era `enum('Bancario','Oxxo','Transferencia')`**. La BD tiene
  `STRICT_TRANS_TABLES` activo, así que el checkout de Mercado Pago (`tipo_pago => 'mercadopago'`,
  valor fuera del enum) lanzaba *"Data truncated for column 'tipo_pago'"* y abortaba la activación
  del plan. Migración `2026_08_30_190000_change_tipo_pago_to_string_on_pagos_table` → `VARCHAR(30)
  NOT NULL DEFAULT 'Bancario'` (conserva los datos `Bancario`/`Transferencia`; `down()` re-normaliza
  y vuelve al enum). Verificado con un `Pago::create(['tipo_pago' => 'mercadopago', ...])` dentro de
  transacción con rollback: **OK** (antes fallaba).
- **Código muerto eliminado**:
  - `app/Http/Controllers/PagoController.php` (legacy, sin ruta; validaba `in:oxxo,banamex,paypal`,
    valores que tampoco existían en el enum).
  - `AdminController::cambiarEstadoExamen()` — sin ruta y referenciaba `$examen->activo`, columna
    inexistente en `Examen_generado`.
- **Revisado y sin problemas**: flujos de pago (`procesarSolicitudPago`, `procesarPagoAprobadoMercadoPago`
  idempotente, webhook público + `VerifyCsrfToken::$except`), notificaciones siempre fuera de las
  transacciones de pago, `NotificacionController` (usa `User::isAdmin()`, que existe), métodos `show`
  de universidades/preguntas/carreras/asignaturas (redirigen o devuelven JSON, no cargan Blades
  borrados), vistas Blade conservadas (`reporte-pdf`, `emails/*`) sin `@extends`/`@include` rotos.
- `npm run build` limpio (`app-*.js` 305 kB, tamaño SAINS); `php artisan test` verde.

## Iteración 15 (notificaciones — rediseño) ✅

- **Se quitó la categoría "Sistema"**: `Notificacion::grupoTipo()` sólo devuelve `pagos` | `cuenta`
  (todo lo que no es pago → cuenta); el chip "Sistema" desapareció de la página.
- **`NotificacionesLista.vue` rediseñado**: fila de 3 tarjetas de resumen (Sin leer / Esta semana /
  En total con iconos degradados), panel con encabezado (chips de categoría con icono + degradado en
  el activo, segmentado Todas/Sin leer, botones "Marcar todas" / "Limpiar leídas"), separadores de día
  con línea y contador, tarjetas con barra de acento lateral por color, "pip" rojo en no leídas,
  acciones que aparecen al hover, animación de entrada escalonada (`.sains-stagger`), estado vacío
  ilustrado. Toda la funcionalidad previa se conserva (filtros, marcar/eliminar, limpiar leídas,
  refresco 60 s, tiempo relativo en vivo).

## Iteración 14 (notificaciones v2) ✅

- **`lib/tiempo.js`** nuevo: reloj compartido (ref `ahora`, tick 30 s) + `desde(iso)` → "hace 3 min" /
  "ayer" / fecha. El tiempo relativo se mantiene fresco sin recargar (campana y página).
- **`NotifBell.vue`**: badge se sincroniza en cada navegación (prop compartido `notificaciones.no_leidas`);
  animación de campana + toast cuando llega una notificación realmente nueva durante el polling
  (compara `maxId` vs último visto); estado vacío ilustrado; barra índigo lateral en no leídas; poll 45 s.
- **Página** (`NotificacionesLista.vue`): chips de tipo (Todas / Pagos / Cuenta / Sistema) con conteo +
  segmentado Todas/Sin leer; acción **"Eliminar leídas"** con confirmación; agrupación por etiqueta
  inteligente (Hoy / Ayer / Esta semana / dd/mm/aaaa); auto-refresco cada 60 s para no quedar
  desincronizada con la campana.
- **Backend**: `Notificacion::paraVista()` agrega `creada_iso`, `grupo` (etiqueta de fecha) y
  `grupo_tipo` (pagos/cuenta/sistema); `NotificacionController::eliminarLeidas` + ruta
  `DELETE notificaciones/leidas` (antes de `{id}`).

## Iteración 13 (clases desde catálogo + limpieza de Blades + favicon) ✅

- **Favicon**: `public/images/favicon.png` (32px) + `favicon-180.png` generados desde el logo
  (el anterior era `logo.png` de 7374×4034 / ~7 MB). `app.blade.php` con `<link rel=icon>` correctos.
- **Clases — video sólo del catálogo**: columna `clases.id_video` (FK a `videos`). El form de clase
  ahora tiene un `<a-select>` de **videos subidos** (los de la materia primero) con preview embebido;
  se quitó el input de URL libre. `ClaseController` valida/guarda `id_video` y copia su `link`.
  Backfill: las 65 clases existentes se vincularon por coincidencia de `link`.
- **Clases — columna del nombre más angosta** (width 300 + ellipsis); la fila muestra
  título y duración del video como subtítulo. Lista ordenada por materia y luego por `num_clase`.
- **N.º de clase automático**: al elegir la materia en "Nueva clase" se consulta
  `admin.clases.siguiente-numero` y se rellena. Además auto-rellena el nombre con el tema del video.
- **Estudiante (lo mismo)**: `ClasesPremium.vue` muestra badge de duración + "Clase X de Y";
  botón "Continuar / Empezar: {siguiente clase}" en el hero; asignaturas ordenadas alfabéticamente.
  `clasesPremium` carga `->with('video')` y pasa `duracion`/`orden`.
- **Limpieza de Blades**: se borró todo `resources/views/administrador/` (salvo
  `estudiantes/reporte-pdf.blade.php`, aún usado por `\PDF::loadView`), `resources/views/estudiante/`,
  `auth/`, `layouts/`, `index.blade.php`, `terms.blade.php`. Se conservan `app.blade.php` + `emails/`.
  Código muerto eliminado: `Alumno\CheckoutController` (no ruteado), `AdminController`
  {usuarios, examenes, pagos, cupones, callcenter, preguntas}, `PreguntaController`
  {preguntas, verAreas, dashboard}, `AlumnoController::verRespuestasExamen`.
- **Notificaciones mejoradas**: página con filtro segmentado (Todas / Sin leer), agrupación por día,
  acciones ✓/✕ por ítem; campana con badge de conteo en el encabezado y "marcar leída" al pasar
  el mouse. Nuevos disparadores: bienvenida al registrarse, perfil completado (estudiante + admins).

## Iteración 12 (notificaciones + Mercado Pago) ✅

**Notificaciones (nuevo sistema, estudiante + admin)**
- Tabla `notificaciones` (`id_usuario`, `tipo`, `titulo`, `mensaje`, `url`, `icono`, `color`,
  `leida_at`) + modelo `App\Models\Notificacion` con `enviar($idUsuario, [...])` y `enviarAdmins([...])`
  (nunca lanzan excepción).
- `NotificacionController` + grupo de rutas `/notificaciones` (`notificaciones.index/feed/leer/
  leer-todas/eliminar`). Página Inertia `Estudiante/Notificaciones.vue` y `Admin/Notificaciones.vue`
  (comparten `Components/NotificacionesLista.vue`).
- `Components/NotifBell.vue`: campana con badge + panel desplegable (poll cada 60 s a `/notificaciones/feed`),
  montada en `EstudianteLayout` y `AdminLayout` (reemplaza la campana de "pagos pendientes" derivada del admin).
- `HandleInertiaRequests` comparte `notificaciones.no_leidas`.
- **Eventos que generan notificaciones**: solicitud de pago (ficha) → estudiante + admins;
  comprobante subido → estudiante + admins; pago aprobado/rechazado por el admin
  (`PagoController::update/aprobar/cambiarEstado`) → estudiante; pago Mercado Pago aprobado
  (`procesarPagoAprobadoMercadoPago`) → estudiante + admins.

**Mercado Pago (checkout del estudiante) — arreglado**
- El webhook (`estudiante/pago/mercadopago/webhook`) ahora va `->withoutMiddleware(['auth'])` y está
  en `VerifyCsrfToken::$except` (lo llama el servidor de MP, sin sesión ni token).
- `pagoExitoMercadoPago` reescrito: verifica el pago contra la API y delega en el helper idempotente
  `procesarPagoAprobadoMercadoPago($payment)` — ya no depende de la sesión; funciona llegue primero el
  redirect o el webhook. Estados pending/in_process → `checkout-pendiente`.
- La preferencia MP lleva `metadata.cupon`; el helper marca ese cupón como usado.
- `.env` / `.env.example`: `MERCADO_PAGO_ACCESS_TOKEN` y `MERCADO_PAGO_PUBLIC_KEY` (vacías → el botón
  de MP se muestra deshabilitado con "No disponible por ahora"; transferencia/OXXO siguen funcionando).
- `Checkout.vue`: métodos de pago rediseñados (radio propio, estado deshabilitado), el botón de MP
  muestra el monto y sólo se activa si hay `mpPublicKey`.

## Iteración 11 (formularios con diseño + relleno aleatorio de exámenes) ✅

- **`Components/FormPage.vue`** nuevo: envuelve los formularios de página completa (a-card +
  barra de acciones `.sains-form-actions` con Cancelar/Guardar). Reemplaza el `<template #actions>`
  mal colocado dentro de `<a-card>` en Pagos, Exámenes, Estudiantes y Preparatorias (Create + Edit).
- **CSS `.sains-form`** en app.css: labels con icono índigo, inputs redondeados + focus índigo,
  `a-divider` de sección con chip `.fsec`, y `.file-drop` (zona de archivo bonita con degradado,
  estado "is-set" verde al elegir archivo).
- **Formularios rediseñados** (iconos + secciones + colores): `PagoForm`, `ExamenForm`,
  `EstudianteForm`, `PreparatoriaForm`. El selector de comprobante feo (`<input type=file>` pelón)
  pasó a `.file-drop`.
- **Pagos/Index**: columna "Pago" (#ID + referencia) → "Referencia" (solo la referencia);
  filtro dedicado por **estudiante** (`state.estudiante`, el controller ya lo soportaba);
  `PagoController` search sólo por `referencia_pago` (se quitó el `orWhere('id', ...)`).
- **Exámenes — relleno aleatorio**: `ExamenForm` tiene un input **N.º de preguntas del examen**
  (objetivo, editable) + switch **"Completar con preguntas aleatorias"** con chips
  Objetivo / Elegidas / Aleatorias. `ExamenGeneradoController::store`/`update` usan
  `armarPreguntas()`: si `completar_aleatorio`, rellena `objetivo - elegidas` preguntas al azar
  del banco (`whereNotIn` las elegidas); si no, exige coincidencia exacta como antes.
  Verificado: examen de 20, 2 elegidas + 18 aleatorias → 20 apoyo_preguntas reales.

## Iteración 10 (modales centrados + alertas + filtros al pie) ✅

- **Modales centrados**: CSS global replica `.ant-modal-centered` sobre todo `.ant-modal-wrap`
  (`::before` + `vertical-align: middle`) → todos los modales quedan centrados vertical y
  horizontalmente sin tocar cada `<a-modal>`.
- **Alertas / toasts**: `.ant-message-custom-content` rediseñado (tarjeta blanca, borde izquierdo
  de color por tipo, icono coloreado, sombra, animación de entrada); `.ant-alert` con fondos suaves
  y sin borde.
- **Filtros al pie de la tabla**: TODOS los índices `.filtered-table` movieron sus inputs de
  búsqueda del `#headerCell` a `#summary` (`<a-table-summary-row>` + `<a-table-summary-cell v-for>`),
  quedando como una fila fija alineada por columna en la parte de abajo de la tabla, encima de la
  paginación. CallCenter dejó de usar `.cc-toolbar` y ahora usa `.filtered-table` + `#summary`
  (con celda vacía inicial por la columna de expandir).

## Iteración 9 (más filtros + diseño de modales) ✅

- **Diseño global**: `.sains-modal` en app.css (header degradado, secciones `.sains-modal__section`,
  footer, inputs redondeados) aplicado a los modales de alta/edición (administradores, cupones,
  clases, videos, preguntas, carreras, universidades, materias, call center, reset-password).
- **CSS**: encabezados ordenables centrados (`th.ant-table-column-has-sorters`) + `.count-pill`
  para columnas numéricas de materias/carreras (alineación corregida).
- **Estudiantes**: filtro dedicado por **teléfono** (busca telefono + telefono_casa).
- **Administradores**: filtros por **correo** y **teléfono**; se quitó la columna "Alta".
- **Exámenes**: `tipo_examen` del filtro sale de valores reales en BD; columna "Creado" con
  fecha **y hora**; Tipo como etiqueta de color.
- **Exámenes realizados**: filtro por **intento**; columna "Fecha y hora" (fecha_inicio + hora_inicio).
- **Preguntas**: filtro por **respuesta correcta**; columna "Justif." como píldora con icono.
- **Cupones**: columna código más angosta (134px) + píldora compacta; filtros de **Expira** y
  **Generó**; modal Compartir rediseñado (hero degradado, ticket, **inputs de teléfono y correo**
  que arman `wa.me/<tel>` y `mailto:<correo>`).
- **Clases**: se quitó la columna "Video"; icono ▶ a la izquierda del nombre abre el modal de video.
- **Videos**: filtro por **tema**; clic en la miniatura reproduce el video; botón **Detectar**
  obtiene la duración (`admin.videos.duracion` → oEmbed Vimeo / `lengthSeconds` de YouTube,
  `Http::withoutVerifying()` por WAMP sin bundle CA).

## Iteración 8 (ajustes UX admin) ✅

- **Cupones**: código como pastilla con copiar, descuento como tag, y acción **Compartir** (modal con
  WhatsApp `wa.me/?text=` + Correo `mailto:` + copiar mensaje).
- **Call Center**: `InteraccionCallCenterController` expone `estado_ultima`; el botón *Retomar* se
  oculta (tag "Finalizado") cuando la última interacción del estudiante está finalizada.
- **Preparatorias**: filtros por `clave` y `municipio` (inputs en el encabezado + `PreparatoriaController`).
- **Universidades**: filtros por `municipio` y `carrera` (`carrera_id`) (+ `UniversidadController`).
- **Materias / Carreras**: orden ascendente/descendente en las columnas de conteo (`sorter:true`
  → `orden_campo`/`orden_direccion`, ya soportado en los controllers).
- **Clases**: se quita la columna `#`; nueva columna **Video** con botón que abre un modal con el
  video embebido (`parseVideo`: vimeo / youtube / drive).
- **Videos**: el índice pasa de grid de tarjetas a tabla (`.filtered-table`) con miniatura + filtros
  en el encabezado (título, materia, visibilidad) + botón "Ver" con modal; `VideoController` filtro `materia`.

## SIGUIENTE

- **Nada crítico pendiente.** Toda la UI de SPA (admin + estudiante + auth) está migrada y verificada.
- `ForgotPasswordController` todavía devuelve JSON (lo consume `AuthModal` con axios) — funciona, opcional migrar.

## Pendientes transversales

- ~~**i18n**~~ ✅ `config/app.php` `locale => 'es'`; creados `lang/es/{validation,auth,passwords,pagination}.php`.
  Verificado: los mensajes de validación ahora salen en español (`El campo correo electrónico no es un correo electrónico válido.`).
  Nota: `config:clear` ejecutado; si en prod se usa `config:cache`, recachear.
- **Vistas faltantes que aún dan 500** (ver Hallazgos): crearlas como páginas Vue al migrar su sección.
- `ConvertEmptyStringsToNull` (middleware global) + columnas `NOT NULL` sin default = errores de integridad. Al migrar cada `store`/`update`, normalizar como en `PreparatoriaController::payload()`.
