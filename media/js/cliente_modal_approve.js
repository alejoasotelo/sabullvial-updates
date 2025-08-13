jQuery(document).ready(function() {    
    Vue.createApp({
        data: function() {
            return {
                monto: '',
                plazo: '',
            }
        },
        mounted() {
            window.parent.setApproveMonto('');
            window.parent.setApprovePlazo('');
        },
        watch: {
            monto: function(newValue, oldValue) {
                window.parent.setApproveMonto(newValue);
            },
            plazo: function(newValue, oldValue) {
                window.parent.setApprovePlazo(newValue);
            }
        }
    }).mount('#modalApproveApp');
    
})