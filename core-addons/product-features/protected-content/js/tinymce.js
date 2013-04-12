(function () {
    tinymce.create("tinymce.plugins.ITExchangeProtectedContentAddon", {
        init: function (a, b) {
            a.addCommand("mceITExchangeProtectedContentAddon", function () {
                a.windowManager.open({
                    file: "?action=it-exchange-protected-content-addon-doing-popup",
                    width: 300,
                    height: 400,
                    inline: 1
                }, {
                    plugin_url: b
                })
            });
            a.addButton("ITExchangeProtectedContentAddon", {
                title: ITExchangeProtectedContentAddonDialog.desc,
                cmd: "mceITExchangeProtectedContentAddon",
                image: b + "/images/lock.png",
            })
        }
    });
    tinymce.PluginManager.add("ITExchangeProtectedContentAddon", tinymce.plugins.ITExchangeProtectedContentAddon);
    if (typeof (tinymce) != "undefined" && typeof (ITExchangeProtectedContentAddonDialog) != "undefined") {
        tinymce.addI18n("en.ITExchangeProtectedContentAddon", ITExchangeProtectedContentAddonDialog)
    }
})();
