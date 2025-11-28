
<template>
    <AppLayout title="Gestión de Usuarios">
        <template #header>
            <h2 class="tema-titulo">Gestión de Usuarios</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <!-- Botón Crear -->
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Usuarios</h3>
                    <button
                        v-if="tienePermiso('crear-usuarios')"
                        @click="abrirModalCrear"
                        class="btn-tema"
                        title="Crear usuario"
                    >
                        +
                    </button>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>DNI</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Tipo Usuario</th>
                                <th>Roles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="usuario in usuarios.data" :key="usuario.id">
                                <td class="tema-texto font-semibold">{{ usuario.persona?.dni || 'N/A' }}</td>
                                <td class="tema-texto font-semibold text-center">{{ usuario.persona ? `${usuario.persona.nombre} ${usuario.persona.apellidos}` : usuario.name }}</td>
                                <td class="tema-texto-secundario text-center">{{ usuario.email }}</td>
                                <td class="text-center">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': usuario.tipo_usuario === 'PROPIETARIO',
                                            'bg-green-100 text-green-800': usuario.tipo_usuario === 'MEDICO',
                                            'bg-yellow-100 text-yellow-800': usuario.tipo_usuario === 'SECRETARIA',
                                            'bg-gray-100 text-gray-800': usuario.tipo_usuario === 'CLIENTE',
                                        }"
                                    >
                                        {{ usuario.tipo_usuario }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        v-for="rol in usuario.roles"
                                        :key="rol.id"
                                        class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs mr-1 mb-1"
                                    >
                                        {{ rol.name }}
                                    </span>
                                    <span v-if="usuario.roles.length === 0" class="tema-texto-secundario text-xs">
                                        Sin roles
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-usuarios')"
                                        @click="abrirModalMostrar(usuario.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar usuario"
                                        aria-label="Mostrar usuario"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-usuarios')"
                                        @click="abrirModalEditar(usuario.id)"
                                        class="btn-tema mr-2"
                                        title="Editar usuario"
                                        aria-label="Editar usuario"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-usuarios')"
                                        @click="eliminarUsuario(usuario.id)"
                                        class="btn-tema-secundario"
                                        tittle="Eliminar usuario"
                                        aria-label="Eliminar usuario"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4" v-if="usuarios.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in usuarios.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-2 mx-1 rounded',
                                link.active ? 'btn-tema' : 'btn-tema-secundario',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con contador de visitas -->
        <footer class="mt-8 py-4 tema-fondo-secundario text-center">
            <p class="tema-texto-secundario">
                Visitas a esta página: <span class="font-bold">{{ contadorVisitas }}</span>
            </p>
        </footer>
    </AppLayout>

    <!-- Modal Crear -->
    <DialogModal :show="modalCrear" @close="cerrarModalCrear">
        <template #title>Crear Usuario</template>

        <template #content>
            <div class="space-y-4">
                <!-- Foto del Usuario -->
                <div class="flex flex-col items-center gap-4">
                    <div class="w-32 h-32 border border-dashed tema-borde rounded-full overflow-hidden flex items-center justify-center bg-gray-50">
                        <img
                            v-if="vistaPreviaFoto"
                            :src="vistaPreviaFoto"
                            alt="Vista previa"
                            class="w-full h-full object-cover"
                        >
                        <span v-else class="tema-texto-secundario text-xs text-center px-2">
                            Sin foto
                        </span>
                    </div>
                    <div class="w-full md:w-1/3">
                        <InputLabel for="usuario-foto" value="Foto del Usuario" class="text-center" />
                        <input
                            id="usuario-foto"
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm"
                            @change="seleccionarFoto"
                        />
                        <p class="text-xs tema-texto-secundario mt-1 text-center">Opcional. Formatos: JPG, PNG (máx. 2MB)</p>
                        <InputError :message="formularioUsuario.errors.foto" class="mt-2 text-center" />
                    </div>
                </div>

                <!-- Datos de Persona -->
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold tema-titulo mb-3 text-center">Datos Personales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="usuario-dni" value="DNI *" />
                            <TextInput id="usuario-dni" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.dni" />
                            <InputError :message="formularioUsuario.errors.dni" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-nombre" value="Nombre *" />
                            <TextInput id="usuario-nombre" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.nombre" />
                            <InputError :message="formularioUsuario.errors.nombre" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-apellidos" value="Apellidos *" />
                            <TextInput id="usuario-apellidos" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.apellidos" />
                            <InputError :message="formularioUsuario.errors.apellidos" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-telefono" value="Teléfono" />
                            <TextInput id="usuario-telefono" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.telefono" />
                            <InputError :message="formularioUsuario.errors.telefono" class="mt-2" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="usuario-direccion" value="Dirección" />
                            <TextInput id="usuario-direccion" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.direccion" />
                            <InputError :message="formularioUsuario.errors.direccion" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-fecha-nacimiento" value="Fecha de Nacimiento" />
                            <TextInput id="usuario-fecha-nacimiento" type="date" class="mt-1 block w-full input-tema" v-model="formularioUsuario.fecha_nacimiento" />
                            <InputError :message="formularioUsuario.errors.fecha_nacimiento" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Datos de Usuario -->
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold tema-titulo mb-3">Datos de Usuario</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="usuario-email" value="Correo Electrónico *" />
                            <TextInput id="usuario-email" type="email" class="mt-1 block w-full input-tema" v-model="formularioUsuario.email" />
                            <InputError :message="formularioUsuario.errors.email" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-tipo" value="Tipo de Usuario *" />
                            <select id="usuario-tipo" class="mt-1 block w-full input-tema" v-model="formularioUsuario.tipo_usuario">
                                <option value="">Seleccione...</option>
                                <option value="PROPIETARIO">Propietario</option>
                                <option value="SECRETARIA">Secretaria</option>
                                <option value="MEDICO">Médico</option>
                                <option value="CLIENTE">Cliente</option>
                            </select>
                            <InputError :message="formularioUsuario.errors.tipo_usuario" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-password" value="Contraseña *" />
                            <TextInput id="usuario-password" type="password" class="mt-1 block w-full input-tema" v-model="formularioUsuario.password" />
                            <InputError :message="formularioUsuario.errors.password" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-password-confirm" value="Confirmar Contraseña *" />
                            <TextInput id="usuario-password-confirm" type="password" class="mt-1 block w-full input-tema" v-model="formularioUsuario.password_confirmation" />
                            <InputError :message="formularioUsuario.errors.password_confirmation" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Campos específicos según tipo -->
                <div v-if="formularioUsuario.tipo_usuario" class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold tema-titulo mb-3">Datos Específicos</h3>

                    <!-- Propietario -->
                    <div v-if="formularioUsuario.tipo_usuario === 'PROPIETARIO'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-nivel-acceso" value="Nivel de Acceso *" />
                            <select id="usuario-nivel-acceso" class="mt-1 block w-full input-tema" v-model="formularioUsuario.nivel_acceso">
                                <option value="TOTAL">Total</option>
                                <option value="PARCIAL">Parcial</option>
                                <option value="LIMITADO">Limitado</option>
                            </select>
                            <InputError :message="formularioUsuario.errors.nivel_acceso" class="mt-2" />
                        </div>
                    </div>

                    <!-- Secretaria -->
                    <div v-if="formularioUsuario.tipo_usuario === 'SECRETARIA'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-turno" value="Turno" />
                            <TextInput id="usuario-turno" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.turno" placeholder="Ej: Mañana, Tarde, Noche" />
                            <InputError :message="formularioUsuario.errors.turno" class="mt-2" />
                        </div>
                    </div>

                    <!-- Médico -->
                    <div v-if="formularioUsuario.tipo_usuario === 'MEDICO'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-especialidades" value="Especialidades *" />
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div 
                                        class="mt-1 block w-full input-tema cursor-pointer"
                                        @click="abrirModalEspecialidades"
                                    >
                                        <span v-if="formularioUsuario.especialidades.length === 0" class="tema-texto-secundario">
                                            Ninguna
                                        </span>
                                        <span v-else class="tema-texto">
                                            {{ obtenerNombresEspecialidades(formularioUsuario.especialidades) }}
                                        </span>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="abrirModalEspecialidades"
                                    class="btn-tema px-3 py-2"
                                    title="Seleccionar especialidades"
                                >
                                    +
                                </button>
                            </div>
                            <InputError :message="formularioUsuario.errors.especialidades" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Horarios de Atención" />
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-end">
                                <div>
                                    <span class="texto-ayuda">Día</span>
                                    <select class="mt-1 block w-full input-tema" v-model="horarioTemporal.dia_semana">
                                        <option v-for="dia in diasSemana" :key="dia.valor" :value="dia.valor">
                                            {{ dia.etiqueta }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <span class="texto-ayuda">Hora Inicio</span>
                                    <input type="time" class="mt-1 block w-full input-tema" v-model="horarioTemporal.hora_inicio" />
                                </div>
                                <div>
                                    <span class="texto-ayuda">Hora Fin</span>
                                    <input type="time" class="mt-1 block w-full input-tema" v-model="horarioTemporal.hora_fin" />
                                </div>
                                <div class="flex justify-end md:justify-start">
                                    <button type="button" class="btn-tema w-full" @click="agregarHorario">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                            <InputError :message="mensajeErrorHorarios()" class="mt-2" />

                            <div class="mt-3 border border-dashed tema-borde rounded p-3">
                                <p v-if="formularioUsuario.horarios.length === 0" class="tema-texto-secundario text-sm">
                                    Aún no hay horarios registrados.
                                </p>
                                <ul v-else class="space-y-2">
                                    <li
                                        v-for="(horario, indice) in formularioUsuario.horarios"
                                        :key="`${horario.dia_semana}-${horario.hora_inicio}-${indice}`"
                                        class="flex items-center justify-between tema-texto"
                                    >
                                        <span>
                                            {{ traducirDia(horario.dia_semana) }}:
                                            {{ horario.hora_inicio }} - {{ horario.hora_fin }}
                                        </span>
                                        <button
                                            type="button"
                                            class="text-red-600 text-sm hover:underline"
                                            @click="eliminarHorario(indice)"
                                        >
                                            Quitar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div>
                            <InputLabel for="usuario-codigo-cmp" value="Código CMP" />
                            <TextInput id="usuario-codigo-cmp" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.codigo_cmp" />
                            <InputError :message="formularioUsuario.errors.codigo_cmp" class="mt-2" />
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div v-if="formularioUsuario.tipo_usuario === 'CLIENTE'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-antecedentes" value="Antecedentes" />
                            <textarea id="usuario-antecedentes" class="mt-1 block w-full input-tema" rows="3" v-model="formularioUsuario.antecedentes"></textarea>
                            <InputError :message="formularioUsuario.errors.antecedentes" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <InputLabel value="Roles" />
                    <div class="max-h-48 overflow-y-auto border border-dashed tema-borde rounded p-3 mt-2">
                        <label
                            v-for="rol in rolesDisponibles"
                            :key="rol.id"
                            class="flex items-center mb-2 tema-texto"
                        >
                            <input type="checkbox" class="mr-2" :value="rol.id" v-model="formularioUsuario.roles" />
                            {{ rol.name }}
                        </label>
                    </div>
                    <InputError :message="formularioUsuario.errors.roles" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarUsuario" :disabled="formularioUsuario.processing">
                Guardar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Editar -->
    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Usuario</template>

            <template #content>
                <div class="space-y-4">
                    <!-- Foto del Usuario -->
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-32 h-32 border border-dashed tema-borde rounded-full overflow-hidden flex items-center justify-center bg-gray-50">
                            <img
                                v-if="vistaPreviaFoto"
                                :src="vistaPreviaFoto"
                                alt="Vista previa"
                                class="w-full h-full object-cover"
                            >
                            <span v-else class="tema-texto-secundario text-xs text-center px-2">
                                Sin foto
                            </span>
                        </div>
                        <div class="w-full md:w-1/3">
                            <InputLabel for="usuario-foto-edit" value="Foto del Usuario" class="text-center" />
                            <input
                                id="usuario-foto-edit"
                                type="file"
                                accept="image/*"
                                class="mt-1 block w-full text-sm"
                                @change="seleccionarFoto"
                            />
                            <p class="text-xs tema-texto-secundario mt-1 text-center">Opcional. Formatos: JPG, PNG (máx. 2MB)</p>
                            <InputError :message="formularioUsuario.errors.foto" class="mt-2 text-center" />
                        </div>
                    </div>

                    <!-- Datos de Persona -->
                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-semibold tema-titulo mb-3 text-center">Datos Personales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="usuario-dni-edit" value="DNI *" />
                            <TextInput id="usuario-dni-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.dni" />
                            <InputError :message="formularioUsuario.errors.dni" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-nombre-edit" value="Nombre *" />
                            <TextInput id="usuario-nombre-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.nombre" />
                            <InputError :message="formularioUsuario.errors.nombre" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-apellidos-edit" value="Apellidos *" />
                            <TextInput id="usuario-apellidos-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.apellidos" />
                            <InputError :message="formularioUsuario.errors.apellidos" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-telefono-edit" value="Teléfono" />
                            <TextInput id="usuario-telefono-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.telefono" />
                            <InputError :message="formularioUsuario.errors.telefono" class="mt-2" />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel for="usuario-direccion-edit" value="Dirección" />
                            <TextInput id="usuario-direccion-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.direccion" />
                            <InputError :message="formularioUsuario.errors.direccion" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-fecha-nacimiento-edit" value="Fecha de Nacimiento" />
                            <TextInput id="usuario-fecha-nacimiento-edit" type="date" class="mt-1 block w-full input-tema" v-model="formularioUsuario.fecha_nacimiento" />
                            <InputError :message="formularioUsuario.errors.fecha_nacimiento" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Datos de Usuario -->
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold tema-titulo mb-3">Datos de Usuario</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="usuario-email-edit" value="Correo Electrónico *" />
                            <TextInput id="usuario-email-edit" type="email" class="mt-1 block w-full input-tema" v-model="formularioUsuario.email" />
                            <InputError :message="formularioUsuario.errors.email" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-tipo-edit" value="Tipo de Usuario *" />
                            <select id="usuario-tipo-edit" class="mt-1 block w-full input-tema" v-model="formularioUsuario.tipo_usuario">
                                <option value="">Seleccione...</option>
                                <option value="PROPIETARIO">Propietario</option>
                                <option value="SECRETARIA">Secretaria</option>
                                <option value="MEDICO">Médico</option>
                                <option value="CLIENTE">Cliente</option>
                            </select>
                            <InputError :message="formularioUsuario.errors.tipo_usuario" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-password-edit" value="Contraseña (opcional)" />
                            <TextInput id="usuario-password-edit" type="password" class="mt-1 block w-full input-tema" v-model="formularioUsuario.password" />
                            <InputError :message="formularioUsuario.errors.password" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-password-confirm-edit" value="Confirmar Contraseña" />
                            <TextInput id="usuario-password-confirm-edit" type="password" class="mt-1 block w-full input-tema" v-model="formularioUsuario.password_confirmation" />
                            <InputError :message="formularioUsuario.errors.password_confirmation" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Campos específicos según tipo -->
                <div v-if="formularioUsuario.tipo_usuario" class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold tema-titulo mb-3">Datos Específicos</h3>

                    <!-- Propietario -->
                    <div v-if="formularioUsuario.tipo_usuario === 'PROPIETARIO'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-nivel-acceso-edit" value="Nivel de Acceso *" />
                            <select id="usuario-nivel-acceso-edit" class="mt-1 block w-full input-tema" v-model="formularioUsuario.nivel_acceso">
                                <option value="TOTAL">Total</option>
                                <option value="PARCIAL">Parcial</option>
                                <option value="LIMITADO">Limitado</option>
                            </select>
                            <InputError :message="formularioUsuario.errors.nivel_acceso" class="mt-2" />
                        </div>
                    </div>

                    <!-- Secretaria -->
                    <div v-if="formularioUsuario.tipo_usuario === 'SECRETARIA'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-turno-edit" value="Turno" />
                            <TextInput id="usuario-turno-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.turno" placeholder="Ej: Mañana, Tarde, Noche" />
                            <InputError :message="formularioUsuario.errors.turno" class="mt-2" />
                        </div>
                    </div>

                    <!-- Médico -->
                    <div v-if="formularioUsuario.tipo_usuario === 'MEDICO'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-especialidades-edit" value="Especialidades *" />
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div 
                                        class="mt-1 block w-full input-tema cursor-pointer"
                                        @click="abrirModalEspecialidades"
                                    >
                                        <span v-if="formularioUsuario.especialidades.length === 0" class="tema-texto-secundario">
                                            Ninguna
                                        </span>
                                        <span v-else class="tema-texto">
                                            {{ obtenerNombresEspecialidades(formularioUsuario.especialidades) }}
                                        </span>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="abrirModalEspecialidades"
                                    class="btn-tema px-3 py-2"
                                    title="Seleccionar especialidades"
                                >
                                    +
                                </button>
                            </div>
                            <InputError :message="formularioUsuario.errors.especialidades" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="usuario-codigo-cmp-edit" value="Código CMP" />
                            <TextInput id="usuario-codigo-cmp-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioUsuario.codigo_cmp" />
                            <InputError :message="formularioUsuario.errors.codigo_cmp" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Horarios de Atención" />
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-end">
                                <div>
                                    <span class="texto-ayuda">Día</span>
                                    <select class="mt-1 block w-full input-tema" v-model="horarioTemporal.dia_semana">
                                        <option v-for="dia in diasSemana" :key="dia.valor" :value="dia.valor">
                                            {{ dia.etiqueta }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <span class="texto-ayuda">Hora Inicio</span>
                                    <input type="time" class="mt-1 block w-full input-tema" v-model="horarioTemporal.hora_inicio" />
                                </div>
                                <div>
                                    <span class="texto-ayuda">Hora Fin</span>
                                    <input type="time" class="mt-1 block w-full input-tema" v-model="horarioTemporal.hora_fin" />
                                </div>
                                <div class="flex justify-end md:justify-start">
                                    <button type="button" class="btn-tema w-full" @click="agregarHorario">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                            <InputError :message="mensajeErrorHorarios()" class="mt-2" />

                            <div class="mt-3 border border-dashed tema-borde rounded p-3">
                                <p v-if="formularioUsuario.horarios.length === 0" class="tema-texto-secundario text-sm">
                                    Aún no hay horarios registrados.
                                </p>
                                <ul v-else class="space-y-2">
                                    <li
                                        v-for="(horario, indice) in formularioUsuario.horarios"
                                        :key="`${horario.dia_semana}-${horario.hora_inicio}-${indice}`"
                                        class="flex items-center justify-between tema-texto"
                                    >
                                        <span>
                                            {{ traducirDia(horario.dia_semana) }}:
                                            {{ horario.hora_inicio }} - {{ horario.hora_fin }}
                                        </span>
                                        <button
                                            type="button"
                                            class="text-red-600 text-sm hover:underline"
                                            @click="eliminarHorario(indice)"
                                        >
                                            Quitar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div v-if="formularioUsuario.tipo_usuario === 'CLIENTE'" class="space-y-4">
                        <div>
                            <InputLabel for="usuario-antecedentes-edit" value="Antecedentes" />
                            <textarea id="usuario-antecedentes-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioUsuario.antecedentes"></textarea>
                            <InputError :message="formularioUsuario.errors.antecedentes" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <InputLabel value="Roles" />
                    <div class="max-h-48 overflow-y-auto border border-dashed tema-borde rounded p-3 mt-2">
                        <label
                            v-for="rol in rolesDisponibles"
                            :key="rol.id"
                            class="flex items-center mb-2 tema-texto"
                        >
                            <input type="checkbox" class="mr-2" :value="rol.id" v-model="formularioUsuario.roles" />
                            {{ rol.name }}
                        </label>
                    </div>
                    <InputError :message="formularioUsuario.errors.roles" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarUsuario" :disabled="formularioUsuario.processing">
                Actualizar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Mostrar -->
    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle del Usuario</template>

        <template #content>
            <div v-if="usuarioMostrado" class="space-y-4">
                <!-- Foto del Usuario -->
                <div class="flex justify-center mb-4">
                    <div class="w-32 h-32 border border-dashed tema-borde rounded-full overflow-hidden flex items-center justify-center bg-gray-50">
                        <img
                            v-if="obtenerRutaFoto(usuarioMostrado)"
                            :src="obtenerRutaFoto(usuarioMostrado)"
                            alt="Foto del usuario"
                            class="w-full h-full object-cover"
                        >
                        <span v-else class="tema-texto-secundario text-xs text-center px-2">
                            Sin foto
                        </span>
                    </div>
                </div>

                <!-- Datos Personales -->
                <div class="border-b pb-3">
                    <h3 class="text-lg font-semibold tema-titulo mb-2">Datos Personales</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <p><strong>DNI:</strong> {{ usuarioMostrado.persona?.dni || 'N/A' }}</p>
                        <p><strong>Nombre:</strong> {{ usuarioMostrado.persona?.nombre || 'N/A' }}</p>
                        <p><strong>Apellidos:</strong> {{ usuarioMostrado.persona?.apellidos || 'N/A' }}</p>
                        <p><strong>Teléfono:</strong> {{ usuarioMostrado.persona?.telefono || 'N/A' }}</p>
                        <p class="col-span-2"><strong>Dirección:</strong> {{ usuarioMostrado.persona?.direccion || 'N/A' }}</p>
                        <p><strong>Fecha Nacimiento:</strong> {{ usuarioMostrado.persona?.fecha_nacimiento || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Datos de Usuario -->
                <div class="border-b pb-3">
                    <h3 class="text-lg font-semibold tema-titulo mb-2">Datos de Usuario</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <p><strong>Email:</strong> {{ usuarioMostrado.email }}</p>
                        <p><strong>Tipo Usuario:</strong>
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                :class="{
                                    'bg-blue-100 text-blue-800': usuarioMostrado.tipo_usuario === 'PROPIETARIO',
                                    'bg-green-100 text-green-800': usuarioMostrado.tipo_usuario === 'MEDICO',
                                    'bg-yellow-100 text-yellow-800': usuarioMostrado.tipo_usuario === 'SECRETARIA',
                                    'bg-gray-100 text-gray-800': usuarioMostrado.tipo_usuario === 'CLIENTE',
                                }"
                            >
                                {{ usuarioMostrado.tipo_usuario }}
                            </span>
                        </p>
                        <p><strong>Estado:</strong> {{ usuarioMostrado.estado ? 'Activo' : 'Inactivo' }}</p>
                    </div>
                </div>

                <!-- Datos Específicos -->
                <div v-if="usuarioMostrado.propietario || usuarioMostrado.secretaria || usuarioMostrado.medico || usuarioMostrado.cliente" class="border-b pb-3">
                    <h3 class="text-lg font-semibold tema-titulo mb-2">Datos Específicos</h3>
                    <div v-if="usuarioMostrado.propietario">
                        <p><strong>Nivel de Acceso:</strong> {{ usuarioMostrado.propietario.nivel_acceso }}</p>
                    </div>
                    <div v-if="usuarioMostrado.secretaria">
                        <p><strong>Turno:</strong> {{ usuarioMostrado.secretaria.turno || 'N/A' }}</p>
                    </div>
                    <div v-if="usuarioMostrado.medico">
                        <div v-if="usuarioMostrado.medico.especialidades && usuarioMostrado.medico.especialidades.length > 0">
                            <p><strong>Especialidades:</strong></p>
                            <ul class="list-disc list-inside">
                                <li v-for="especialidad in usuarioMostrado.medico.especialidades" :key="especialidad.id">
                                    {{ especialidad.nombre }}
                                </li>
                            </ul>
                        </div>
                        <p v-else><strong>Especialidades:</strong> Ninguna</p>
                        <p><strong>Código CMP:</strong> {{ usuarioMostrado.medico.codigo_cmp || 'N/A' }}</p>
                        <div class="mt-2">
                            <p class="font-semibold">Horarios:</p>
                            <ul v-if="usuarioMostrado.medico.horarios && usuarioMostrado.medico.horarios.length > 0" class="list-disc list-inside">
                                <li v-for="horario in usuarioMostrado.medico.horarios" :key="horario.id">
                                    {{ traducirDia(horario.dia_semana) }}: {{ formatearHoraVisual(horario.hora_inicio) }} - {{ formatearHoraVisual(horario.hora_fin) }}
                                </li>
                            </ul>
                            <p v-else class="tema-texto-secundario text-sm">Sin horarios registrados</p>
                        </div>
                        <p class="mt-2"><strong>Observaciones:</strong> {{ usuarioMostrado.medico.horario_atencion || 'N/A' }}</p>
                    </div>
                    <div v-if="usuarioMostrado.cliente">
                        <p><strong>Antecedentes:</strong> {{ usuarioMostrado.cliente.antecedentes || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <h3 class="text-lg font-semibold tema-titulo mb-2">Roles</h3>
                    <div class="mt-2">
                        <span
                            v-for="rol in usuarioMostrado.roles"
                            :key="rol.id"
                            class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs mr-1 mb-1"
                        >
                            {{ rol.name }}
                        </span>
                        <span v-if="usuarioMostrado.roles.length === 0" class="tema-texto-secundario text-sm">
                            Sin roles asignados
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <PrimaryButton @click="cerrarModalMostrar">
                Cerrar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Seleccionar Especialidades -->
    <DialogModal :show="modalEspecialidades" @close="cerrarModalEspecialidades">
        <template #title>Seleccionar Especialidades</template>
        <template #content>
            <div class="max-h-64 overflow-y-auto border border-dashed tema-borde rounded p-3">
                <label
                    v-for="especialidad in especialidadesDisponibles"
                    :key="especialidad.id"
                    class="flex items-center mb-2 tema-texto cursor-pointer hover:bg-gray-50 p-2 rounded"
                >
                    <input
                        type="checkbox"
                        class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        :value="especialidad.id"
                        v-model="formularioUsuario.especialidades"
                    />
                    <span>{{ especialidad.nombre }}</span>
                </label>
                <p v-if="especialidadesDisponibles.length === 0" class="tema-texto-secundario text-center py-4">
                    No hay especialidades disponibles
                </p>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEspecialidades">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="cerrarModalEspecialidades">Aceptar</PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, onMounted, watch, reactive } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    usuarios: Object,
    roles: Array,
    especialidades: Array,
    contadorVisitas: Number,
});

const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const modalEspecialidades = ref(false);
const estaCargandoEdicion = ref(false);
const usuarioMostrado = ref(null);
const rolesDisponibles = ref(props.roles || []);
const especialidadesDisponibles = ref([]);
const vistaPreviaFoto = ref(null);
const diasSemana = [
    { valor: 'LUNES', etiqueta: 'Lunes' },
    { valor: 'MARTES', etiqueta: 'Martes' },
    { valor: 'MIERCOLES', etiqueta: 'Miércoles' },
    { valor: 'JUEVES', etiqueta: 'Jueves' },
    { valor: 'VIERNES', etiqueta: 'Viernes' },
    { valor: 'SABADO', etiqueta: 'Sábado' },
    { valor: 'DOMINGO', etiqueta: 'Domingo' },
];
const horarioTemporal = reactive({
    dia_semana: diasSemana[0].valor,
    hora_inicio: '',
    hora_fin: '',
});
const errorHorariosManual = ref('');

watch(() => props.roles, (nuevos) => {
    rolesDisponibles.value = nuevos || [];
});

onMounted(() => {
    especialidadesDisponibles.value = props.especialidades || [];
});

const formularioUsuario = useForm({
    id: null,
    // Datos de Persona
    dni: '',
    nombre: '',
    apellidos: '',
    telefono: '',
    direccion: '',
    fecha_nacimiento: '',
    // Datos de Usuario
    email: '',
    password: '',
    password_confirmation: '',
    tipo_usuario: '',
    foto: null,
    // Datos específicos según tipo
    nivel_acceso: 'TOTAL',
    turno: '',
    especialidades: [],
    horarios: [],
    codigo_cmp: '',
    horario_atencion: '',
    antecedentes: '',
    // Roles
    roles: [],
});

function construirUrlStorage(rutaRelativa) {
    if (!rutaRelativa) {
        return null;
    }
    const base = (typeof window !== 'undefined' && window.location?.origin) ? window.location.origin : '';
    const limpia = rutaRelativa.replace(/^storage\//, '').replace(/^\/+/, '');
    return `${base}/storage/${limpia}`;
}

function obtenerRutaFoto(objeto) {
    if (!objeto) {
        return null;
    }
    if (objeto.profile_photo_url) {
        return objeto.profile_photo_url;
    }
    if (objeto.foto_url) {
        if (objeto.foto_url.startsWith('http')) {
            return objeto.foto_url;
        }
        return construirUrlStorage(objeto.foto_url);
    }
    return null;
}

// Limpiar campos específicos cuando cambia el tipo de usuario
function limpiarCamposEspecificos() {
    formularioUsuario.nivel_acceso = 'TOTAL';
    formularioUsuario.turno = '';
    formularioUsuario.especialidades = [];
    formularioUsuario.horarios = [];
    formularioUsuario.codigo_cmp = '';
    formularioUsuario.horario_atencion = '';
    formularioUsuario.antecedentes = '';
    formularioUsuario.foto = null;

    vistaPreviaFoto.value = null;
}

function reiniciarHorarioTemporal() {
    horarioTemporal.dia_semana = diasSemana[0].valor;
    horarioTemporal.hora_inicio = '';
    horarioTemporal.hora_fin = '';
}

function agregarHorario() {
    errorHorariosManual.value = '';

    if (!horarioTemporal.hora_inicio || !horarioTemporal.hora_fin) {
        errorHorariosManual.value = 'Debe ingresar la hora de inicio y fin.';
        return;
    }

    if (horarioTemporal.hora_fin <= horarioTemporal.hora_inicio) {
        errorHorariosManual.value = 'La hora de fin debe ser mayor a la hora de inicio.';
        return;
    }

    formularioUsuario.horarios.push({
        dia_semana: horarioTemporal.dia_semana,
        hora_inicio: horarioTemporal.hora_inicio,
        hora_fin: horarioTemporal.hora_fin,
    });

    reiniciarHorarioTemporal();
}

function eliminarHorario(indice) {
    formularioUsuario.horarios.splice(indice, 1);
}

function seleccionarFoto(evento) {
    const archivo = evento.target.files[0];

    if (!archivo) {
        formularioUsuario.foto = null;
        vistaPreviaFoto.value = null;
        return;
    }

    formularioUsuario.foto = archivo;

    const lector = new FileReader();
    lector.onload = (e) => {
        vistaPreviaFoto.value = e.target.result;
    };
    lector.readAsDataURL(archivo);
}

function mensajeErrorHorarios() {
    if (errorHorariosManual.value) {
        return errorHorariosManual.value;
    }

    const claves = Object.keys(formularioUsuario.errors).filter((clave) => clave.startsWith('horarios'));
    if (claves.length > 0) {
        return formularioUsuario.errors[claves[0]];
    }

    return '';
}

function traducirDia(dia) {
    const encontrado = diasSemana.find((d) => d.valor === dia);
    return encontrado ? encontrado.etiqueta : dia;
}

function formatearHoraVisual(hora) {
    if (!hora) {
        return '';
    }

    if (typeof hora === 'string') {
        // Si viene en formato HH:MM
        if (/^\d{2}:\d{2}$/.test(hora)) {
            return hora;
        }
        // Si viene como 2025-11-27T08:00:00 o similar
        if (hora.includes('T')) {
            const [, timePart] = hora.split('T');
            if (timePart) {
                return timePart.slice(0, 5);
            }
        }
        // Si viene como "2025-11-27 08:00:00"
        if (hora.includes(' ')) {
            const partes = hora.split(' ');
            if (partes[1]) {
                return partes[1].slice(0, 5);
            }
        }
    }

    const fecha = new Date(hora);
    if (!isNaN(fecha.getTime())) {
        const horas = fecha.getHours().toString().padStart(2, '0');
        const minutos = fecha.getMinutes().toString().padStart(2, '0');
        return `${horas}:${minutos}`;
    }

    return '';
}

// Watch para limpiar campos cuando cambia el tipo de usuario
watch(() => formularioUsuario.tipo_usuario, (nuevoTipo, tipoAnterior) => {
    if (estaCargandoEdicion.value) {
        return;
    }
    if (tipoAnterior && nuevoTipo !== tipoAnterior) {
        limpiarCamposEspecificos();
    }
});

watch(() => formularioUsuario.password, (nuevaContrasena) => {
    if (nuevaContrasena && nuevaContrasena.length > 0) {
        formularioUsuario.password_confirmation = nuevaContrasena;
    }
});

function abrirModalCrear() {
    estaCargandoEdicion.value = false;
    formularioUsuario.reset();
    formularioUsuario.clearErrors();
    formularioUsuario.roles = [];
    formularioUsuario.especialidades = [];
    formularioUsuario.horarios = [];
    formularioUsuario.foto = null;
    formularioUsuario.tipo_usuario = '';
    formularioUsuario.nivel_acceso = 'TOTAL';
    errorHorariosManual.value = '';
    reiniciarHorarioTemporal();
    vistaPreviaFoto.value = null;
    
    // Cargar especialidades disponibles
    axios.get(route('usuarios.create'))
        .then(response => {
            especialidadesDisponibles.value = response.data.especialidades || [];
            rolesDisponibles.value = response.data.roles || [];
            modalCrear.value = true;
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            modalCrear.value = true;
        });
}

function abrirModalMostrar(id) {
    axios.get(route('usuarios.show', id))
        .then(response => {
            usuarioMostrado.value = response.data.usuario;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    estaCargandoEdicion.value = true;
    formularioUsuario.clearErrors();
    axios.get(route('usuarios.edit', id))
        .then(response => {
            const { usuario, roles } = response.data;
            formularioUsuario.id = usuario.id;

            // Datos de Persona
            if (usuario.persona) {
                formularioUsuario.dni = usuario.persona.dni || '';
                formularioUsuario.nombre = usuario.persona.nombre || '';
                formularioUsuario.apellidos = usuario.persona.apellidos || '';
                formularioUsuario.telefono = usuario.persona.telefono || '';
                formularioUsuario.direccion = usuario.persona.direccion || '';
                formularioUsuario.fecha_nacimiento = usuario.persona.fecha_nacimiento || '';
            }

            // Datos de Usuario
            formularioUsuario.email = usuario.email || '';
            formularioUsuario.tipo_usuario = usuario.tipo_usuario || '';
            formularioUsuario.password = '';
            formularioUsuario.password_confirmation = '';

            // Datos específicos según tipo
            if (usuario.propietario) {
                formularioUsuario.nivel_acceso = usuario.propietario.nivel_acceso || 'TOTAL';
            }
            if (usuario.secretaria) {
                formularioUsuario.turno = usuario.secretaria.turno || '';
            }
            if (usuario.medico) {
                // Cargar especialidades asignadas al médico
                if (usuario.medico.especialidades && Array.isArray(usuario.medico.especialidades)) {
                    formularioUsuario.especialidades = usuario.medico.especialidades.map(esp => esp.id);
                } else {
                    formularioUsuario.especialidades = response.data.especialidadesAsignadas || [];
                }

                // Cargar horarios del médico
                if (usuario.medico.horarios && Array.isArray(usuario.medico.horarios)) {
                    formularioUsuario.horarios = usuario.medico.horarios.map((horario) => ({
                        dia_semana: horario.dia_semana,
                        hora_inicio: formatearHoraVisual(horario.hora_inicio),
                        hora_fin: formatearHoraVisual(horario.hora_fin),
                    }));
                } else {
                    formularioUsuario.horarios = [];
                }
                formularioUsuario.codigo_cmp = usuario.medico.codigo_cmp || '';
                formularioUsuario.horario_atencion = usuario.medico.horario_atencion || '';
            } else {
                formularioUsuario.especialidades = [];
                formularioUsuario.horarios = [];
            }
            
            // Cargar especialidades disponibles
            if (response.data.especialidades) {
                especialidadesDisponibles.value = response.data.especialidades;
            }
            errorHorariosManual.value = '';
            reiniciarHorarioTemporal();
            vistaPreviaFoto.value = obtenerRutaFoto(usuario);
            if (usuario.cliente) {
                formularioUsuario.antecedentes = usuario.cliente.antecedentes || '';
            }

            // Roles
            formularioUsuario.roles = usuario.roles?.map((rol) => rol.id) || [];
            if (roles) {
                rolesDisponibles.value = roles;
            }
            modalEditar.value = true;
        })
        .catch(() => {
            // En caso de error, aseguramos reinicio del estado
            estaCargandoEdicion.value = false;
        })
        .finally(() => {
            estaCargandoEdicion.value = false;
        });
}

function guardarUsuario() {
    console.log('🔍 [Crear Usuario] Datos del formulario:', formularioUsuario.data());
    console.log('🔍 [Crear Usuario] Errores actuales:', formularioUsuario.errors);
    console.log('🔍 [Crear Usuario] Procesando:', formularioUsuario.processing);
    
    formularioUsuario.post(route('usuarios.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (page) => {
            console.log('✅ [Crear Usuario] Éxito - Respuesta:', page);
            cerrarModalCrear();
        },
        onError: (errors) => {
            console.error('❌ [Crear Usuario] Error - Errores de validación:', errors);
            console.error('❌ [Crear Usuario] Errores del formulario:', formularioUsuario.errors);
        },
        onFinish: () => {
            console.log('🏁 [Crear Usuario] Finalizado - Estado:', {
                processing: formularioUsuario.processing,
                errors: formularioUsuario.errors,
            });
        },
    });
}

function actualizarUsuario() {
    formularioUsuario
        .transform((data) => ({
            ...data,
            _method: 'PUT',
        }))
        .post(route('usuarios.update', formularioUsuario.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                cerrarModalEditar();
            },
            onFinish: () => {
                formularioUsuario.transform((data) => data);
            },
        });
}

function eliminarUsuario(id) {
    if (confirm('¿Está seguro de eliminar este usuario?')) {
        router.delete(route('usuarios.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioUsuario.reset();
    formularioUsuario.clearErrors();
    limpiarCamposEspecificos();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioUsuario.reset();
    formularioUsuario.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    usuarioMostrado.value = null;
};

// Funciones para modal de especialidades
function abrirModalEspecialidades() {
    modalEspecialidades.value = true;
}

function cerrarModalEspecialidades() {
    modalEspecialidades.value = false;
}

// Función para obtener nombres de especialidades seleccionadas
function obtenerNombresEspecialidades(idsEspecialidades) {
    if (!idsEspecialidades || idsEspecialidades.length === 0) {
        return 'Ninguna';
    }
    
    const nombres = idsEspecialidades.map(id => {
        const especialidad = especialidadesDisponibles.value.find(esp => esp.id === id);
        return especialidad ? especialidad.nombre : '';
    }).filter(nombre => nombre !== '');
    
    return nombres.length > 0 ? nombres.join(', ') : 'Ninguna';
}

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

<style scoped>
/* Los estilos usan las variables CSS del tema */
</style>

