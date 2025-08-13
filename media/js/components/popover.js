// Vue 3 component to display a popover bootstrap 2.3.2 style
function popoverComponent() {
    return {
        props: {
            placement: {
                type: String,
                default: 'top'
            },
            title: {
                type: String,
                default: ''
            },
            content: {
                type: String,
                default: ''
            },
            trigger: {
                type: String,
                default: 'hover focus'
            },
            delay: {
                type: Number,
                default: 0
            },
            animation: {
                type: Boolean,
                default: true
            },
            html: {
                type: Boolean,
                default: false
            },
            container: {
                type: String,
                default: ''
            },
            selector: {
                type: String,
                default: ''
            },
        },
        template: `<div :title="title" :data-content="content" :data-placement="placement" :data-trigger="trigger" :data-delay="delay" :data-animation="animation" :data-html="html" :data-container="container" :data-selector="selector" data-toggle="popover">
            <slot></slot>
        </div>`,
        mounted() {
            const $el = jQuery(this.$el);
            $el.popover();
            $el.on('shown.bs.popover', () => {
                // hide all other popovers
                jQuery('[data-toggle="popover"]').not($el).popover('hide');
            });
        },
        destroyed() {
            jQuery(this.$el).popover('destroy');
        }
    };
}