/*!
FullCalendar Bootstrap 4 Plugin v6.1.10
Docs & License: https://fullcalendar.io/docs/bootstrap4
(c) 2023 Adam Shaw
*/
FullCalendar.Bootstrap = (function (exports, core, internal$1) {
    'use strict';

    class BootstrapTheme extends internal$1.Theme {
    }
    BootstrapTheme.prototype.classes = {
        root: 'fc-theme-bootstrap',
        table: 'table table-bordered',
        tableCellShaded: 'table-active',
        buttonGroup: 'btn-group btn-yesno radio',
        button: 'btn',
        buttonActive: 'active btn-success',
        popover: 'popover',
        popoverHeader: 'popover-header',
        popoverContent: 'popover-body',
    };
    // BootstrapTheme.prototype.baseIconClass = 'fa';
    BootstrapTheme.prototype.iconClasses = {
        close: 'icon-cancel',
        prev: 'icon-chevron-left',
        next: 'icon-chevron-right',
        prevYear: 'icon-first',
        nextYear: 'icon-last',
    };
    BootstrapTheme.prototype.rtlIconClasses = {
        prev: 'icon-chevron-right',
        next: 'icon-chevron-left',
        prevYear: 'icon-last',
        nextYear: 'icon-first',
    };
    // BootstrapTheme.prototype.iconOverrideOption = 'bootstrapFontAwesome'; // TODO: make TS-friendly. move the option-processing into this plugin
    // BootstrapTheme.prototype.iconOverrideCustomButtonOption = 'bootstrapFontAwesome';
    // BootstrapTheme.prototype.iconOverridePrefix = '';

    var css_248z = ".fc-theme-bootstrap a:not([href]){color:inherit}.fc-theme-bootstrap .fc-more-link:hover{text-decoration:none}";
    internal$1.injectStyles(css_248z);

    var plugin = core.createPlugin({
        name: '@fullcalendar/bootstrap',
        themeClasses: {
            bootstrap: BootstrapTheme,
        },
    });

    var internal = {
        __proto__: null,
        BootstrapTheme: BootstrapTheme
    };

    core.globalPlugins.push(plugin);

    exports.Internal = internal;
    exports["default"] = plugin;

    Object.defineProperty(exports, '__esModule', { value: true });

    return exports;

})({}, FullCalendar, FullCalendar.Internal);
