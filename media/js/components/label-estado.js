function labelEstadoComponent() {
    return {
        props: {
            backgroundColor: {
                type: String,
                default: ''
            },
            color: {
                type: String,
                default: ''
            },
        },
        computed: {
            style() {
                return 'background-color: ' + this.backgroundColor + '; color: ' + this.color + ';';
            }
        },
        template: `
            <span :style="style" class="label">
                <slot></slot>
            </span>
        `,
    };
}
