jQuery(document).ready(function() {    
    Vue.createApp({
        data: function() {
            return {
                comentarios: ''
            }
        },
        mounted() {
            window.parent.setApproveComentarios('');
        },
        watch: {
            comentarios: function(newValue, oldValue) {
                window.parent.setApproveComentarios(newValue);
            }
        }
    }).mount('#modalApproveApp');
    
})