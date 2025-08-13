function buttonYesNoComponent() {
    return {
        props: {
            modelValue: Boolean | Number,
            name: String,
            labelYes: {
                type: String,
                default: 'Si'
            },
            labelYesClass: {
                type: String,
                default: 'btn-success'
            },
            labelNo: {
                type: String,
                default: 'No'
            },
            labelNoClass: {
                type: String,
                default: 'btn-danger'
            },
            showLabel: {
                type: Boolean,
                default: true
            },
            controlsClass: {
                type: String,
                default: 'btn-group-yesno'
            }
        },
        emits: ['update:model-value', 'change'],
        data: function () {
            return {
                value: this.modelValue
            };
        },
        computed: {
            id() {
                return this.name.replace('[', '_').replace(']', '');
            },
            id0() {
                return this.id + '0';
            },
            id1() {
                return this.id + '1';
            }
        },
        template: `<div class="control-group">
                        <div v-if="showLabel" class="control-label">
                            <label :for="id"><slot></slot></label>
                        </div>
                        <div class="controls">
                            <fieldset :id="id" class="btn-group radio" :class="controlsClass">
                                <label :for="id0" class="btn" :class="{[labelYesClass]: value == 1 || value === true}" @click="setValue(1)">
                                    <input type="radio" :id="id0" :name="name" value="1"> {{ labelYes }}
                                </label>

                                <label :for="id1" class="btn" :class="{[labelNoClass]: value == 0 || value === false}" @click="setValue(0)">
                                    <input type="radio" :id="id1" :name="name" value="0"> {{ labelNo }}
                                </label>
                            </fieldset>
                        </div>
                    </div>`,
        methods: {
            setValue: function (value) {
                // if (value == this.modelValue) {
                //     return;
                // }
                this.value = value;
                this.$emit('update:model-value', this.value);
                this.$emit('change', this.value);
            }
        },
        watch: {
          modelValue(newValue) {
            this.value = newValue; // Actualiza el modelo interno cuando cambia la prop
          },
        },
    };
}