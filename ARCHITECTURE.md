# ARCHITECTURE.md - Masterclass

Plataforma d'aprenentatge interactiu basada en vídeos de YouTube amb preguntes dinàmiques (model tipus Edpuzzle). Dissenyada per a hosting compartit (cdmon) amb PHP nativ i MySQL, sense frameworks.

---

## Stack Tecnològic

| Capa | Tecnologia |
|------|-----------|
| Backend | PHP nativ (sense frameworks) |
| Base de dades | MySQL (`guiamanudb`, utf8mb4, InnoDB) |
| Accés a dades | PDO amb prepared statements |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| Vídeo | YouTube IFrame API |
| Hosting | cdmon (hosting compartit) |
| Autenticació | PHP Sessions + `password_hash()` / `password_verify()` |

---

## Estructura de Directoris

```
masterclass/
├── index.php                  # Front Controller (router central)
│
├── config/
│   └── Database.php           # Connexió PDO singleton (MySQL)
│
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php     # Login / Logout / Sessió
│   │   ├── PreguntaController.php # Obtenir preguntes d'un vídeo
│   │   ├── RespostaController.php # Guardar respostes d'alumnes
│   │   └── VideoController.php    # Vídeos assignats a un alumne
│   └── Models/
│       ├── Pregunta.php           # Model de preguntes
│       ├── Resposta.php           # Model de respostes
│       ├── Usuari.php             # Model d'usuaris
│       └── Video.php              # Model de vídeos
│
├── api/
│   ├── get_preguntes.php          # API legacy (PDO directe)
│   └── post_resposta.php          # API legacy (PDO directe)
│
├── public/
│   ├── css/
│   │   ├── style.css              # Estils globals del reproductor
│   │   └── dashboard_alumne.css   # Estils del panell d'alumne
│   ├── js/
│   │   ├── app.js                 # JS legacy del reproductor
│   │   ├── dashboard_alumne.js    # JS del panell d'alumne
│   │   ├── login.js               # JS del formulari de login
│   │   └── reproductor.js         # JS actual del reproductor
│   ├── login.html                 # Pàgina de login
│   ├── dashboard_alumne.html      # Panell de l'alumne
│   └── reproductor.html           # Reproductor interactiu
│
├── sql/
│   ├── script1.sql                # DDL: taules principals + seeders
│   ├── script2.sql                # DDL: taula usuaris + seeders
│   └── script3.sql                # DDL: taula usuari_videos + seeders
│
├── bin/
│   └── generar_hash.php           # Utilitat per generar hashes de contrasenyes
│
├── index.html                     # Versió standalone legacy
└── index_[1-5].html               # Prototips incrementals de desenvolupament
```

---

## Patró Arquitectònic: Front Controller + MVC

Totes les peticions HTTP passen per `index.php`, que actua com a router central. Les rutes es defineixen mitjançant el paràmetre GET `?action=`.

### Flux de treball

```
Navegador → index.php?action=X
                ├── Controller → Model → Database (PDO/MySQL)
                └── Retorna JSON o serveix HTML
```

---

## Enrutament (Routing)

Definit al `switch ($action)` de `index.php`:

| Ruta | Mètode | Controlador | Descripció |
|------|--------|------------|-----------|
| `api/get_preguntes` | GET | `PreguntaController::index()` | Obté preguntes i opcions d'un vídeo |
| `api/post_resposta` | POST | `RespostaController::store()` | Guarda resposta (mock alumne_id=1) |
| `api/post_resposta_alumne` | POST | `RespostaController::store_resposta_alumne()` | Guarda resposta amb sessió activa |
| `api/login` | POST | `AuthController::login()` | Autenticació d'usuari |
| `logout` | GET | `AuthController::logout()` | Tancar sessió |
| `api/get_videos_alumne` | GET | `VideoController::getVideosAlumne()` | Vídeos assignats a l'alumne |
| *(default)* | GET | Serveix HTML | Login / Dashboard / Reproductor segons sessió i paràmetres |

### Ruta del Reproductor

El reproductor s'accedeix via: `index.php?video={id}` (el paràmetre `video` es tracta al `default` del switch).

---

## Model de Dades (MySQL)

Base de dades: `guiamanudb` · Charset: `utf8mb4` · Engine: `InnoDB`

### Relacions entre Taules

```
mc_usuaris ──────────► mc_usuari_videos ◄────────── mc_videos
                                          │
                                          ▼
                                     mc_preguntes ──────► mc_opcions_pregunta
                                          │
                                          ▼
                                  mc_respostes_alumnes ◄── mc_usuaris
```

### Taules

#### `mc_videos`
| Camp | Tipus | Descripció |
|------|-------|-----------|
| id | INT AUTO_INCREMENT PK | Identificador |
| codi_youtube | VARCHAR(50) | Codi del vídeo de YouTube |
| titol | VARCHAR(255) | Títol del vídeo |
| descripcio | TEXT | Descripció (nullable) |
| data_creacio | TIMESTAMP | Data de creació |

#### `mc_preguntes`
| Camp | Tipus | Descripció |
|------|-------|-----------|
| id | INT AUTO_INCREMENT PK | Identificador |
| video_id | INT NOT NULL FK → mc_videos(id) CASCADE | Vídeo associat |
| segon | INT NOT NULL | Segon del vídeo on apareix |
| tipus | ENUM('text','single','multiple') | Tipus de pregunta |
| text_pregunta | TEXT NOT NULL | Enunciat |

#### `mc_opcions_pregunta`
| Camp | Tipus | Descripció |
|------|-------|-----------|
| id | INT AUTO_INCREMENT PK | Identificador |
| pregunta_id | INT NOT NULL FK → mc_preguntes(id) CASCADE | Pregunta associada |
| text_opcio | VARCHAR(255) | Text de l'opció |
| es_correcta | TINYINT(1) DEFAULT 0 | 1 si és la resposta correcta |

#### `mc_respostes_alumnes`
| Camp | Tipus | Descripció |
|------|-------|-----------|
| id | INT AUTO_INCREMENT PK | Identificador |
| pregunta_id | INT NOT NULL FK → mc_preguntes(id) CASCADE | Pregunta resposta |
| alumne_id | INT NOT NULL | ID de l'alumne |
| resposta_text | TEXT NULL | Resposta per a preguntes de tipus text |
| opcio_seleccionada_id | INT NULL FK → mc_opcions_pregunta(id) SET NULL | Opció seleccionada |
| data_resposta | TIMESTAMP | Data de la resposta |

#### `mc_usuaris`
| Camp | Tipus | Descripció |
|------|-------|-----------|
| id | INT AUTO_INCREMENT PK | Identificador |
| username | VARCHAR(50) UNIQUE | Nom d'usuari |
| email | VARCHAR(100) UNIQUE | Correu electrònic |
| password_hash | VARCHAR(255) | Hash bcrypt de la contrasenya |
| nom | VARCHAR(50) | Nom |
| cognoms | VARCHAR(100) | Cognoms |
| rol | ENUM('alumne','professor') | Rol de l'usuari |
| data_creacio | TIMESTAMP | Data de creació |

#### `mc_usuari_videos` (taula de relació)
| Camp | Tipus | Descripció |
|------|-------|-----------|
| id | INT AUTO_INCREMENT PK | Identificador |
| usuari_id | INT NOT NULL FK → mc_usuaris(id) CASCADE | Usuari |
| video_id | INT NOT NULL FK → mc_videos(id) CASCADE | Vídeo |
| estat | ENUM('pendent','vist','completat') | Estat de visualització |
| reproduccions_restants | INT DEFAULT 3 | Reproduccions restants |
| data_limit | DATETIME NULL | Data de caducitat |
| data_completat | TIMESTAMP NULL | Data de completament |
| UNIQUE | (usuari_id, video_id) | Evita duplicats |

---

## Autenticació i Sessions

- **Login**: `AuthController::login()` verifica email + `password_hash` amb `password_verify()`
- **Sessió PHP**: `$_SESSION['usuari_id']`, `$_SESSION['nom']`, `$_SESSION['rol']`
- **Control d'accés**: `VideoController` requereix sessió activa amb rol `alumne`
- **CORS**: Headers configurats al Front Controller per a peticions `fetch()`

### Usuaris de prova

| Rol | Email | Contrasenya |
|-----|-------|-------------|
| alumne | alumne@masterclass.com | alumne123 |
| professor | profe@masterclass.com | profe123 |

---

## Funcionalitats Principals

### 1. Reproductor Interactiu
- Integració amb YouTube IFrame API
- Preguntes que apareixen automàticament en segons específics del vídeo
- Sistema anti-salt (bloqueja el seeker si hi ha preguntes sense respondre)
- Suport per a 3 tipus de pregunta:
  - `text` — resposta de text lliure
  - `single` — selecció d'opció única (radio)
  - `multiple` — selecció múltiple (checkbox)

### 2. Panell d'Alumne (Dashboard)
- Llista de vídeos assignats amb estat (`pendent` / `vist` / `completat` / `caducat`)
- Control de reproduccions restants
- Control de data de caducitat
- Botó de logout

### 3. Login
- Formulari AJAX (fetch) amb feedback d'errors
- Redirecció per rols: alumne → dashboard, professor → missatge

---

## Seguretat

- **Prepared statements** (PDO) per a totes les consultes SQL
- **`password_hash()` / `password_verify()`** per a contrasenyes
- **`htmlspecialchars()`** per a l'output HTML
- **CORS headers** configurats al Front Controller
- **Sessió PHP** per al control d'accés server-side
- **Transaccions PDO** amb rollback en cas d'error

---

## Nota sobre Codi Legacy

Els fitxers `index_1.html` fins a `index_5.html` són prototips incrementals del desenvolupament. L'API a `api/get_preguntes.php` i `api/post_resposta.php` és codi legacy amb PDO directe (no fa servir el patró MVC). El codi actual i actiu usa el Front Controller (`index.php`) amb Controllers i Models.
