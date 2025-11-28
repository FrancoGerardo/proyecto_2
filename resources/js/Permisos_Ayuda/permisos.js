// Helper para verificar permisos en componentes Vue

/**
 * Verifica si el usuario tiene un permiso específico
 * @param {string} permiso - Nombre del permiso a verificar
 * @returns {boolean}
 */
export function tienePermiso(permiso) {
    // Obtener el usuario desde las props compartidas de Inertia
    const usuario = window.$page?.()?.props?.auth?.user;

    // Si no hay usuario, no tiene permisos
    if (!usuario) return false;

    // Verificar si el permiso está en el array de permisos del usuario
    return usuario.permissions?.includes(permiso) || false;
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param {string} rol - Nombre del rol a verificar
 * @returns {boolean}
 */
export function tieneRol(rol) {
    const usuario = window.$page?.()?.props?.auth?.user;
    if (!usuario) return false;

    // Verificar si el rol está en el array de roles del usuario
    return usuario.roles?.includes(rol) || false;
}

/**
 * Verifica si el usuario tiene AL MENOS UNO de los permisos especificados
 * @param {string[]} permisos - Array de permisos a verificar
 * @returns {boolean}
 */
export function tieneAlgunPermiso(permisos) {
    const usuario = window.$page?.()?.props?.auth?.user;
    if (!usuario) return false;

    // Verificar si alguno de los permisos está en el array
    return permisos.some(permiso => usuario.permissions?.includes(permiso));
}

/**
 * Verifica si el usuario tiene TODOS los permisos especificados
 * @param {string[]} permisos - Array de permisos a verificar
 * @returns {boolean}
 */
export function tieneTodosLosPermisos(permisos) {
    const usuario = window.$page?.()?.props?.auth?.user;
    if (!usuario) return false;

    // Verificar si todos los permisos están en el array
    return permisos.every(permiso => usuario.permissions?.includes(permiso));
}
