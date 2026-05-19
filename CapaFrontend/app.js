// Frontend/app.js

new Vue({
    el: '#app',
    vuetify: new Vuetify({
        theme: {
            themes: {
                light: theme,
            },
        },
    }),
    data: {
        currentPage: 'login',
        loginForm: {
            email: '',
            password: '',
        },
        registerForm: {
            name: '',
            email: '',
            password: '',
        }
    },
    methods: {
        switchTo(page) {
            this.currentPage = page;
        },
        register() {
            // Aquí iría la lógica para enviar los datos a un backend.
            // Por ahora, solo cambiamos a la pantalla de éxito.
            console.log('Registrando usuario:', this.registerForm);
            this.switchTo('success');
            // Limpiamos el formulario después del registro
            this.registerForm = { name: '', email: '', password: '' };
        }
    }
});
