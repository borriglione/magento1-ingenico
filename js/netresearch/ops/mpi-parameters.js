var MpiParameter = Class.create();

MpiParameter.prototype = {
    initialize: function () {
        this.browserColorDepth = window.screen.colorDepth.toString();
        this.browserJavaEnabled = navigator.javaEnabled() ? 'true' : 'false';
        this.browserLanguage = navigator.language;
        this.browserScreenHeight = window.screen.height.toString();
        this.browserScreenWidth = window.screen.width.toString();
        this.browserTimeZone = (new Date()).getTimezoneOffset().toString();
    },

    get: function (acceptHeader) {
        return {
            browserColorDepth: this.browserColorDepth,
            browserJavaEnabled: this.browserJavaEnabled,
            browserLanguage: this.browserLanguage,
            browserScreenHeight: this.browserScreenHeight,
            browserScreenWidth: this.browserScreenWidth,
            browserTimeZone: this.browserTimeZone,
            browserAcceptHeader: acceptHeader
        };
    }
};
