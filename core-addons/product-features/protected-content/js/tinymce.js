(function () {
    tinymce.create("tinymce.plugins.ITCartBuddyProtectedContentAddon", {
        init: function (a, b) {
            a.addCommand("mceITCartBuddyProtectedContentAddon", function () {
                a.windowManager.open({
                    file: "?action=it-cart-buddy-protected-content-addon-doing-popup",
                    width: 300,
                    height: 400,
                    inline: 1
                }, {
                    plugin_url: b
                })
            });
            a.addButton("ITCartBuddyProtectedContentAddon", {
                title: ITCartBuddyProtectedContentAddonDialog.desc,
                cmd: "mceITCartBuddyProtectedContentAddon",
                image: b + "/images/lock.png",
            })
        }
    });
    tinymce.PluginManager.add("ITCartBuddyProtectedContentAddon", tinymce.plugins.ITCartBuddyProtectedContentAddon);
    if (typeof (tinymce) != "undefined" && typeof (ITCartBuddyProtectedContentAddonDialog) != "undefined") {
        tinymce.addI18n("en.ITCartBuddyProtectedContentAddon", ITCartBuddyProtectedContentAddonDialog)
    }
})();
