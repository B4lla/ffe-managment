CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nombre, descripcion) VALUES
    (1, 'Administrador', 'Gestion total de la aplicacion'),
    (2, 'Direccion', 'Firma convenios por parte del centro'),
    (3, 'Coordinador FFE', 'Gestion de convenios por departamento'),
    (4, 'Profesor tutor', 'Gestion y validacion de convenios asignados'),
    (5, 'Profesor', 'Consulta de convenios sin modificacion'),
    (6, 'Secretaria', 'Generacion y carga documental de convenios'),
    (7, 'Empresa externa', 'Acceso limitado a sus propios convenios y datos');

ALTER TABLE roles AUTO_INCREMENT = 8;

CREATE TABLE departamentos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    familia_profesional VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE usuarios (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre TEXT NOT NULL,
    email TEXT NOT NULL,
    dni_cif VARCHAR(255) NULL,
    email_hash CHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    foto_url TEXT,
    activo TINYINT(1) DEFAULT 1,
    departamento_id BIGINT NULL,
    rol_id BIGINT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ciclos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    grado VARCHAR(50) NOT NULL,
    departamento_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cursos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    anio INT NOT NULL,
    ciclo_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ciclo_id) REFERENCES ciclos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alumnos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    dni VARCHAR(15) UNIQUE,
    ciclo_id BIGINT NOT NULL,
    curso_id BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ciclo_id) REFERENCES ciclos(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE empresas (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nombre_razon_social VARCHAR(300) NOT NULL,
    dni_cif VARCHAR(20) UNIQUE NOT NULL,
    actividad TEXT,
    categoria VARCHAR(50),
    tipo VARCHAR(50),
    email VARCHAR(150),
    telefono1 VARCHAR(20),
    telefono2 VARCHAR(20),
    provincia VARCHAR(100),
    municipio VARCHAR(100),
    direccion TEXT,
    codigo_postal VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE centros_trabajo (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    empresa_id BIGINT NOT NULL,
    direccion TEXT,
    municipio VARCHAR(100),
    provincia VARCHAR(100),
    codigo_postal VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE representantes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    empresa_id BIGINT NOT NULL,
    nif VARCHAR(15),
    nombre VARCHAR(150),
    apellido1 VARCHAR(100),
    apellido2 VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tutores_empresa (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    empresa_id BIGINT NOT NULL,
    nombre_completo VARCHAR(250) NOT NULL,
    dni VARCHAR(15),
    email VARCHAR(150),
    telefono VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE convenios (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    num_convenio VARCHAR(50) UNIQUE,
    empresa_id BIGINT NOT NULL,
    profesor_id BIGINT NULL,
    representante_id BIGINT NULL,
    resp_gestion_nombre VARCHAR(200),
    resp_gestion_telefono VARCHAR(20),
    resp_gestion_email VARCHAR(150),
    resp_ies_nombre VARCHAR(200),
    resp_ies_telefono VARCHAR(20),
    resp_ies_email VARCHAR(150),
    fecha_firma DATE,
    estado VARCHAR(50) DEFAULT 'borrador',
    horario_practicas VARCHAR(255) NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (representante_id) REFERENCES representantes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE empresa_contacto_familia (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    empresa_id BIGINT NOT NULL,
    departamento_id BIGINT NOT NULL,
    profesor_id BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE convenio_tutor_empresa (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    convenio_id BIGINT NOT NULL,
    tutor_empresa_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_convenio_tutor (convenio_id, tutor_empresa_id),
    FOREIGN KEY (convenio_id) REFERENCES convenios(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_empresa_id) REFERENCES tutores_empresa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE convenio_ciclo (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    convenio_id BIGINT NOT NULL,
    ciclo_id BIGINT NOT NULL,
    plazas INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_convenio_ciclo (convenio_id, ciclo_id),
    FOREIGN KEY (convenio_id) REFERENCES convenios(id) ON DELETE CASCADE,
    FOREIGN KEY (ciclo_id) REFERENCES ciclos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE alumno_convenio (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    convenio_id BIGINT NOT NULL,
    alumno_id BIGINT NOT NULL,
    tutor_empresa_id BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_convenio_alumno (convenio_id, alumno_id),
    FOREIGN KEY (convenio_id) REFERENCES convenios(id) ON DELETE CASCADE,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_empresa_id) REFERENCES tutores_empresa(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE horarios_practicas (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tutor_empresa_id BIGINT NOT NULL,
    slot_numero INT NOT NULL,
    horario VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_empresa_id) REFERENCES tutores_empresa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registro_contactos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    empresa_id BIGINT NOT NULL,
    profesor_id BIGINT NOT NULL,
    resultado VARCHAR(100),
    observaciones TEXT,
    fecha_contacto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documentos_pdf (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    convenio_id BIGINT NOT NULL,
    subido_por BIGINT NULL,
    tipo VARCHAR(50) NOT NULL,
    estado_doc VARCHAR(50) DEFAULT 'valido',
    ruta_archivo TEXT NOT NULL,
    es_erroneo TINYINT(1) DEFAULT 0,
    motivo_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (convenio_id) REFERENCES convenios(id) ON DELETE CASCADE,
    FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tareas_pendientes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    convenio_id BIGINT NULL,
    usuario_id BIGINT NULL,
    tipo_tarea VARCHAR(100),
    descripcion TEXT,
    completada TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (convenio_id) REFERENCES convenios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;