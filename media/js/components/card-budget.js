function cardBudgetComponent() {
    return {
        props: {
            title: { type: String, required: true },
            titleInfo: { type: String, default: '' },
            badge: { type: String, default: '' },
            badgeType: { type: String, default: 'info' },
            description: { type: String, default: '' },
            targetAmount: { type: Number, required: true },
            sessionKey: { type: String, required: true },
            currentAmount: { type: Number, default: 0 }
        },
        data() {
            return {
                currentAmountValue: this.currentAmount,
                debouncedSaveCurrentAmount: null
            }
        },
        watch: {
            currentAmountValue() {
                if (!this.debouncedSaveCurrentAmount) {
                    this.debouncedSaveCurrentAmount = Tools.debounce(this.saveCurrentAmount, 400);
                }
                this.debouncedSaveCurrentAmount();
            }
        },
        computed: {
            stepNumber() {
                return this.targetAmount ? Math.max(1, Math.round(this.targetAmount / 100)) : 1;
            },
            badgeTypeClass() {
                const validTypes = ['success', 'warning', 'info', 'danger', 'important'];
                let type = this.badgeType;

                if (type == 'danger') {
                    type = 'important';
                }

                return validTypes.includes(type) ? `label-${type}` : 'label-info';
            },
            max() {
                return Math.round(this.targetAmount);
            },
            barra() {
                if (!this.targetAmount || this.targetAmount <= 0 || !this.currentAmountValue || this.currentAmountValue <= 0) {
                    return { porcentaje: 0, color: '#d32f2f' };
                }

                const actual = parseFloat(this.currentAmountValue) || 0;
                const objetivo = parseFloat(this.targetAmount) || 0;
                const porcentaje = Math.round((actual / objetivo) * 100);

                const colores = [
                    '#d32f2f',
                    '#f57c32',
                    '#fbc02d',
                    '#fdd835',
                    '#ffee58',
                    '#cddc39',
                    '#388e3c',
                ];

                const indexColor = Math.floor(porcentaje / (100 / colores.length));

                const color = indexColor < colores.length ? colores[indexColor] : colores[colores.length - 1];

                return { porcentaje: porcentaje, color };
            },
            barraStyle() {
                return {
                    width: this.barra.porcentaje + '%',
                    background: this.barra.color,
                    transition: 'all 0.5s'
                };
            }
        },
        methods: {
            async saveCurrentAmount() {
                const comOptions = Joomla.getOptions('com_sabullvial');
                const data = {
                    key: this.sessionKey,
                    value: this.currentAmountValue
                };

                try {
                    await jQuery.post(
                        'index.php?option=com_sabullvial&task=dashboardscrm.saveCurrentAmount&' +
                        comOptions.token + '=1',
                        data
                    );
                } catch (e) {
                    console.error('Error', e);
                }
            }
        },
        template: `
            <div class="card-budget">
                <div class="card-tarea mb-3">
                    <h1 class="text-info">
                        {{ title }}
                        <i v-if="titleInfo" class="icon-info font-normal muted h3 hasTooltip" :title="titleInfo"></i>
                    </h1>
                    <p v-if="badge">
                        <span class="label" :class="badgeTypeClass">{{ badge }}</span>
                    </p>
                    <p v-if="description" class="muted">{{ description }}</p>
                </div>
                <div class="form-horizontal">
                    <div class="control-group mb-1">
                        <div class="control-label">
                            <label>Comparar con objetivo:</label>
                        </div>
                        <div class="controls" style="margin-left: 165px;">
                            <input type="number" min="0" :max="max" v-model.number="currentAmountValue" :step="stepNumber" style="float: left;">
                        </div>
                    </div>
                    <div class="progress">
                        <div class="bar" :style="barraStyle"></div>
                    </div>
                    <span v-if="currentAmountValue && targetAmount && targetAmount > 0" style="font-size:12px;">{{ barra.porcentaje }}%</span>
                </div>
            </div>
        `
    }
}

