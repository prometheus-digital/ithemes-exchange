(function () {
    tinymce.create("tinymce.plugins.ITCartBuddyRestrictedContentAddon", {
        init: function (a, b) {
            a.addCommand("mceITCartBuddyRestrictedContentAddon", function () {
                a.windowManager.open({
                    file: "?action=it-cart-buddy-restricted-content-addon-doing-popup",
                    width: 300,
                    height: 400,
                    inline: 1
                }, {
                    plugin_url: b
                })
            });
            a.addButton("ITCartBuddyRestrictedContentAddon", {
                title: ITCartBuddyRestrictedContentAddonDialog.desc,
                cmd: "mceITCartBuddyRestrictedContentAddon",
                image: b + "/images/lock.png",
            })
        }
    });
    tinymce.PluginManager.add("ITCartBuddyRestrictedContentAddon", tinymce.plugins.ITCartBuddyRestrictedContentAddon);
    if (typeof (tinymce) != "undefined" && typeof (ITCartBuddyRestrictedContentAddonDialog) != "undefined") {
        tinymce.addI18n("en.ITCartBuddyRestrictedContentAddon", ITCartBuddyRestrictedContentAddonDialog)
    }
})();
