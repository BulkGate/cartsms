<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div id="cart-sms">
        <nav>
            <div class="container-fluid">
                <div class="nav-wrapper">
                    <div id="brand-logo">
                        <a class="brand-logo hide-on-med-and-down" href="<?= \BulkGate\Extensions\Escape::htmlAttr($homepage) ?>">
                            <img alt="prestasms" width="120" src="<?= \BulkGate\Extensions\Escape::htmlAttr($logo) ?>" />
                        </a>
                    </div>
                    <ul class="controls">
                        <span id="react-app-panel-admin-buttons"></span>
                        <span id="react-app-info"></span>
                    </ul>
                    <div class="nav-h1">
                        <span class="h1-divider"></span>
                        <h1 class="truncate"><?= \BulkGate\Extensions\Escape::html($title) ?><span id="react-app-h1-sub"></span></h1>
                    </div>
                </div>
            </div>
        </nav>
        <div id="profile-tab"></div>
        <div<?php if($box): ?> class="module-box"<?php endif; ?>>
            <div id="react-snack-root"></div>
            <div id="react-app-root">
                <div class="loader loading">
                    <div class="spinner"></div>
                    <p>Loading content</p>
                </div>
            </div>
            <div id="react-language-footer"></div>
            <script type="application/javascript">
                var _bg_client_config = {
                    url: {
                        authenticationService : <?= \BulkGate\Extensions\Escape::js($authenticate) ?>
                    }
                };
            </script>

            <script src="<?= \BulkGate\Extensions\Escape::htmlAttr($widget_api_url) ?>"></script>
            <script type="application/javascript">
                _bg_client.registerMiddleware(function (data)
                {
                    if(data.init._generic)
                    {
                        data.init.env.homepage.logo_link = <?= \BulkGate\Extensions\Escape::js($logo) ?>;
                        data.init._generic.scope.module_info = <?= \BulkGate\Extensions\Escape::js($info) ?>;
                    }
                });

                var input = _bg_client.parseQuery(location.search);

                _bg_client.require(<?= \BulkGate\Extensions\Escape::js($application_id) ?>, {
                    product: "oc",
                    language: <?= \BulkGate\Extensions\Escape::js($language) ?>,
                    view: {
                        presenter: <?= \BulkGate\Extensions\Escape::js($presenter) ?>,
                        action: <?= \BulkGate\Extensions\Escape::js($action) ?>,
                    },
                    params: {
                        id: <?php if(isset($id)): \BulkGate\Extensions\Escape::js($id); else: ?> input["id"] <?php endif; ?>,
                        key: <?php if(isset($key)): \BulkGate\Extensions\Escape::js($key); else: ?> input["key"] <?php endif; ?>,
                        type: <?php if(isset($type)): \BulkGate\Extensions\Escape::js($type); else: ?> input["type"] <?php endif; ?>,
                    },
                    proxy: <?= \BulkGate\Extensions\Escape::js($proxy) ?>,
                });
            </script>
        </div>
    </div>
</div>
<?php echo $footer; ?>
