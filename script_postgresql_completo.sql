-- ============================================
-- SCRIPT SQL POSTGRESQL - CLÍNICA SANTIAGO APÓSTOL
-- Base de datos completa sin errores
-- ============================================

-- Eliminar tablas si existen (en orden inverso de dependencias)
DROP TABLE IF EXISTS auditoria CASCADE;
DROP TABLE IF EXISTS reportes CASCADE;
DROP TABLE IF EXISTS preferencias_tema CASCADE;
DROP TABLE IF EXISTS items_menu CASCADE;
DROP TABLE IF EXISTS pagos CASCADE;
DROP TABLE IF EXISTS metodos_pago CASCADE;
DROP TABLE IF EXISTS planes_cuota CASCADE;
DROP TABLE IF EXISTS historiales_clinicos CASCADE;
DROP TABLE IF EXISTS seguimientos CASCADE;
DROP TABLE IF EXISTS horarios_medicos CASCADE;
DROP TABLE IF EXISTS medico_servicios CASCADE;
DROP TABLE IF EXISTS medico_especialidad CASCADE;
DROP TABLE IF EXISTS fichas CASCADE;
DROP TABLE IF EXISTS servicios CASCADE;
DROP TABLE IF EXISTS especialidades CASCADE;
DROP TABLE IF EXISTS salas CASCADE;
DROP TABLE IF EXISTS clientes CASCADE;
DROP TABLE IF EXISTS medicos CASCADE;
DROP TABLE IF EXISTS secretarias CASCADE;
DROP TABLE IF EXISTS propietarios CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS personas CASCADE;

-- ============================================
-- TABLA: personas
-- ============================================
CREATE TABLE personas (
    id VARCHAR(50) PRIMARY KEY,
    dni VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_nacimiento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE usuarios (
    id VARCHAR(50) PRIMARY KEY,
    persona_id VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password_hash VARCHAR(255) NOT NULL,
    foto_url VARCHAR(255),
    tipo_usuario VARCHAR(20) NOT NULL CHECK (tipo_usuario IN ('PROPIETARIO', 'SECRETARIA', 'MEDICO', 'CLIENTE')),
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remember_token VARCHAR(255),
    current_team_id VARCHAR(50),
    profile_photo_path VARCHAR(2048),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_persona FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE
);

CREATE INDEX idx_usuarios_tipo_usuario ON usuarios(tipo_usuario);

-- ============================================
-- TABLA: propietarios
-- ============================================
CREATE TABLE propietarios (
    usuario_id VARCHAR(50) PRIMARY KEY,
    nivel_acceso VARCHAR(50) DEFAULT 'TOTAL',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_propietarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: secretarias
-- ============================================
CREATE TABLE secretarias (
    usuario_id VARCHAR(50) PRIMARY KEY,
    turno VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_secretarias_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: medicos
-- ============================================
CREATE TABLE medicos (
    usuario_id VARCHAR(50) PRIMARY KEY,
    codigo_cmp VARCHAR(50) UNIQUE,
    horario_atencion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medicos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: clientes
-- ============================================
CREATE TABLE clientes (
    usuario_id VARCHAR(50) PRIMARY KEY,
    antecedentes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_clientes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: especialidades
-- ============================================
CREATE TABLE especialidades (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(256),
    estado VARCHAR(20) DEFAULT 'ACTIVA' CHECK (estado IN ('ACTIVA', 'INACTIVA')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLA: servicios
-- ============================================
CREATE TABLE servicios (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(20) NOT NULL CHECK (categoria IN ('INTERNACION', 'ESPECIALIDAD', 'ENFERMERIA')),
    especialidad_id VARCHAR(50),
    medico_id VARCHAR(50),
    costo DECIMAL(10,2) NOT NULL,
    duracion_minutos INTEGER,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_servicios_especialidad FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE SET NULL,
    CONSTRAINT fk_servicios_medico FOREIGN KEY (medico_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ============================================
-- TABLA: salas
-- ============================================
CREATE TABLE salas (
    id VARCHAR(50) PRIMARY KEY,
    numero VARCHAR(20) UNIQUE NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    equipamiento TEXT,
    estado VARCHAR(20) NOT NULL CHECK (estado IN ('DISPONIBLE', 'OCUPADA', 'MANTENIMIENTO', 'INACTIVA')),
    capacidad INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLA: fichas
-- ============================================
CREATE TABLE fichas (
    id VARCHAR(50) PRIMARY KEY,
    cliente_id VARCHAR(50) NOT NULL,
    servicio_id VARCHAR(50) NOT NULL,
    medico_id VARCHAR(50) NOT NULL,
    sala_id VARCHAR(50),
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado VARCHAR(20) NOT NULL CHECK (estado IN ('PENDIENTE', 'CONFIRMADA', 'ATENDIDA', 'CANCELADA')),
    motivo_consulta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fichas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(usuario_id) ON DELETE CASCADE,
    CONSTRAINT fk_fichas_servicio FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    CONSTRAINT fk_fichas_medico FOREIGN KEY (medico_id) REFERENCES medicos(usuario_id) ON DELETE CASCADE,
    CONSTRAINT fk_fichas_sala FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE SET NULL
);

-- ============================================
-- TABLA: seguimientos
-- ============================================
CREATE TABLE seguimientos (
    id VARCHAR(50) PRIMARY KEY,
    ficha_id VARCHAR(50) NOT NULL,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('TRIAGE', 'CONSULTA', 'TRATAMIENTO')),
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    signos_vitales JSONB,
    motivo_consulta TEXT,
    nivel_urgencia VARCHAR(20) CHECK (nivel_urgencia IN ('BAJA', 'MEDIA', 'ALTA', 'URGENTE')),
    diagnostico TEXT,
    observaciones TEXT,
    tratamiento_prescrito TEXT,
    medicamentos JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_seguimientos_ficha FOREIGN KEY (ficha_id) REFERENCES fichas(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: historiales_clinicos
-- ============================================
CREATE TABLE historiales_clinicos (
    id VARCHAR(50) PRIMARY KEY,
    cliente_id VARCHAR(50) UNIQUE NOT NULL,
    alergias TEXT,
    enfermedades_cronicas TEXT,
    medicamentos_habituales TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historiales_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(usuario_id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: planes_cuota
-- ============================================
CREATE TABLE planes_cuota (
    id VARCHAR(50) PRIMARY KEY,
    ficha_id VARCHAR(50) NOT NULL,
    numero_cuotas INTEGER NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    monto_cuota DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    intervalo_dias INTEGER NOT NULL DEFAULT 30,
    estado VARCHAR(20) NOT NULL CHECK (estado IN ('ACTIVO', 'PAGADO', 'MOROSO', 'CANCELADO')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_planes_cuota_ficha FOREIGN KEY (ficha_id) REFERENCES fichas(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: metodos_pago
-- ============================================
CREATE TABLE metodos_pago (
    id VARCHAR(50) PRIMARY KEY,
    usuario_id VARCHAR(50) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titular VARCHAR(100),
    numero_tarjeta_enmascarado VARCHAR(50),
    banco VARCHAR(100),
    numero_cuenta VARCHAR(50),
    datos_adicionales JSONB,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    predeterminado BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_metodos_pago_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE INDEX idx_metodos_pago_usuario ON metodos_pago(usuario_id);
CREATE INDEX idx_metodos_pago_activo ON metodos_pago(activo);

-- ============================================
-- TABLA: pagos
-- ============================================
CREATE TABLE pagos (
    id VARCHAR(50) PRIMARY KEY,
    plan_cuota_id VARCHAR(50),
    ficha_id VARCHAR(50) NOT NULL,
    metodo_pago_id VARCHAR(50),
    monto DECIMAL(10,2) NOT NULL,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('CONTADO', 'CUOTA', 'ABONO')),
    numero_cuota INTEGER,
    fecha_pago TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metodo_pago VARCHAR(20) NOT NULL CHECK (metodo_pago IN ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'QR')),
    comprobante_url VARCHAR(255),
    estado VARCHAR(20) NOT NULL CHECK (estado IN ('PENDIENTE', 'PAGADO', 'ANULADO')),
    -- Campos PagoFácil
    pagofacil_transaction_id VARCHAR(100),
    company_transaction_id VARCHAR(100),
    qr_base64 TEXT,
    qr_status VARCHAR(20) DEFAULT 'PENDING',
    qr_expiration TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagos_plan_cuota FOREIGN KEY (plan_cuota_id) REFERENCES planes_cuota(id) ON DELETE SET NULL,
    CONSTRAINT fk_pagos_ficha FOREIGN KEY (ficha_id) REFERENCES fichas(id) ON DELETE CASCADE,
    CONSTRAINT fk_pagos_metodo_pago FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id) ON DELETE SET NULL
);

-- ============================================
-- TABLA: medico_especialidad (Muchos a Muchos)
-- ============================================
CREATE TABLE medico_especialidad (
    id VARCHAR(50) PRIMARY KEY,
    medico_id VARCHAR(50) NOT NULL,
    especialidad_id VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medico_especialidad_medico FOREIGN KEY (medico_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_medico_especialidad_especialidad FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE,
    CONSTRAINT uk_medico_especialidad UNIQUE (medico_id, especialidad_id)
);

-- ============================================
-- TABLA: medico_servicios (Muchos a Muchos)
-- ============================================
CREATE TABLE medico_servicios (
    id VARCHAR(50) PRIMARY KEY,
    medico_id VARCHAR(50) NOT NULL,
    servicio_id VARCHAR(50) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medico_servicios_medico FOREIGN KEY (medico_id) REFERENCES medicos(usuario_id) ON DELETE CASCADE,
    CONSTRAINT fk_medico_servicios_servicio FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    CONSTRAINT uk_medico_servicios UNIQUE (medico_id, servicio_id)
);

-- ============================================
-- TABLA: horarios_medicos
-- ============================================
CREATE TABLE horarios_medicos (
    id VARCHAR(50) PRIMARY KEY,
    medico_id VARCHAR(50) NOT NULL,
    dia_semana VARCHAR(20) NOT NULL CHECK (dia_semana IN ('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO')),
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_horarios_medicos_medico FOREIGN KEY (medico_id) REFERENCES medicos(usuario_id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: items_menu
-- ============================================
CREATE TABLE items_menu (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    icono VARCHAR(100),
    orden INTEGER NOT NULL DEFAULT 0,
    permiso_requerido VARCHAR(100),
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    item_padre_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_menu_padre FOREIGN KEY (item_padre_id) REFERENCES items_menu(id) ON DELETE CASCADE
);

CREATE INDEX idx_items_menu_orden ON items_menu(orden);
CREATE INDEX idx_items_menu_activo ON items_menu(activo);

-- ============================================
-- TABLA: preferencias_tema
-- ============================================
CREATE TABLE preferencias_tema (
    id VARCHAR(50) PRIMARY KEY,
    usuario_id VARCHAR(50) UNIQUE NOT NULL,
    tema VARCHAR(20) NOT NULL DEFAULT 'adultos',
    modo VARCHAR(20) NOT NULL DEFAULT 'dia',
    tamaño_fuente VARCHAR(20) NOT NULL DEFAULT 'normal',
    contraste VARCHAR(20) NOT NULL DEFAULT 'normal',
    modo_auto BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_preferencias_tema_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: reportes
-- ============================================
CREATE TABLE reportes (
    id VARCHAR(50) PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('FINANCIERO', 'CLINICO', 'OPERATIVO')),
    nombre VARCHAR(100) NOT NULL,
    parametros JSONB,
    url_archivo VARCHAR(255),
    estado VARCHAR(20) NOT NULL CHECK (estado IN ('GENERANDO', 'COMPLETADO', 'ERROR')),
    usuario_generador VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reportes_usuario FOREIGN KEY (usuario_generador) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================
-- TABLA: auditoria
-- ============================================
CREATE TABLE auditoria (
    id BIGSERIAL PRIMARY KEY,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id VARCHAR(50) NOT NULL,
    accion VARCHAR(10) NOT NULL CHECK (accion IN ('INSERT', 'UPDATE', 'DELETE')),
    usuario_id VARCHAR(50),
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_auditoria_tabla_registro ON auditoria(tabla_afectada, registro_id);

-- ============================================
-- COMENTARIOS EN TABLAS (Opcional)
-- ============================================
COMMENT ON TABLE personas IS 'Información personal de todas las personas del sistema';
COMMENT ON TABLE usuarios IS 'Usuarios del sistema con sus credenciales y tipo';
COMMENT ON TABLE fichas IS 'Citas médicas de los clientes';
COMMENT ON TABLE pagos IS 'Pagos realizados por los clientes, incluye integración con PagoFácil';
COMMENT ON TABLE servicios IS 'Servicios médicos ofrecidos por la clínica';

-- ============================================
-- FIN DEL SCRIPT
-- ============================================

