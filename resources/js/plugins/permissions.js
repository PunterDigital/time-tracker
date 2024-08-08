// plugins/permissions.js

export default {
    install: (app) => {
        app.config.globalProperties.$can = function(permission) {
            const user = this.$page.props.auth.user;

            if (!user) return false;

            const userPermissions = user.permissions || [];
            const userRoles = user.role;

            // Check if permission is a wild card permission
            if (userPermissions.length === 1 && userPermissions[0] === '*') {
                return true;
            }

            return userPermissions.includes(permission) ||
                userRoles.some(role => {
                    return role.permissions && role.permissions.includes(permission);
                });
        }

        app.config.globalProperties.$hasRole = function(role) {
            const user = this.$page.props.auth.user;
            if (!user) return false;

            return user.role[0] === role;
        }
    }
}
