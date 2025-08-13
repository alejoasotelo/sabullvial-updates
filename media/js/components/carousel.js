// Vue 3 component to display a popover bootstrap 2.3.2 style
function carouselComponent() {
    return {
        props: {
            images: {
                type: Array,
                default: []
            },
        },
        template: `<a @click.native.prevent="showCarousel">
            <slot></slot>
        </a>`,
        mounted() {
            const $el = jQuery(this.$el);
        },
        methods: {
            showCarousel(e) {
                e.stopPropagation();

                var body = `<br/>
                    <div class='splide' role='group' aria-label='Splide Basic HTML Example'>
                    <div class='splide__track'>
                            <ul class='splide__list'>`;

                            Object.entries(this.images).forEach(([key, value]) => {
                                body += `<li class="splide__slide">
                                            <div class="splide__slide__container">
                                                <img src="${value.path}"  width="320" height="320" />
                                            </div>
                                        </li>`;
                            });

                body += `</ul>
                    </div>
                    <div class='my-carousel-progress'>
                    <div class='my-carousel-progress-bar'></div>
                </div>
                    </div><br/>
                `;

                var modalProductoCarousel = jQuery('#modalProductoCarousel');

                modalProductoCarousel.find('.modal-body').html(body);
                
                let modalSplide = new Splide('.splide', {
                    type: 'loop',
                    autoHeight: true,
                    gap: '12px',  
                    focus: 'center',
                })

                modalProductoCarousel.on('hidden.bs.modal', function (e) {
                    modalSplide.destroy('completely');
                }).on('shown.bs.modal', function (e) {
                    modalSplide.mount();
                });

                modalProductoCarousel.modal('show');

                return false;
            }
        }
    };
}