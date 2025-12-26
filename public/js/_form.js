(function ($) {
    function makeid(length) {
        var result = "";

        var characters =
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        var charactersLength = characters.length;

        for (var i = 0; i < length; i++) {
            result += characters.charAt(
                Math.floor(Math.random() * charactersLength)
            );
        }

        return result;
    }

    $(document).ready(function () {
        if ($(".dungdt-select2-field").length > 0) {
            $(".dungdt-select2-field").each(function () {
                var configs = $(this).data("options");
                if (configs?.ajax) {
                    configs.ajax.processResults = function (data) {
                        return {
                            results: data,
                        };
                    };
                }
                configs.width = "100%";
                if (configs.ajax?.data) {
                    var configsData = configs.ajax.data;
                    configs.ajax.data = function (params) {
                        SearchParams = {};
                        $.each(configsData, function (key, selector) {
                            if ($(selector).is("select")) {
                                SearchParams[key] = $(selector)
                                    .find(":selected")
                                    .val();
                            } else {
                                SearchParams[key] = $(selector).val() ?? selector;
                            }
                        });
                        return Object.assign(SearchParams, {
                            search: params.term,
                        });
                    };
                }
                if (configs?.dropdownParent) {
                    configs.dropdownParent = $(configs.dropdownParent);
                }
                $(this).select2(configs);
            });
        }
    });

    $(document).on("re-select2", ".dungdt-select2-field", function () {
        var configs = $(this).data("options");
        if (configs?.ajax) {
            configs.ajax.processResults = function (data) {
                return {
                    results: data,
                };
            };
        }
        if (configs.ajax?.data) {
            var configsData = configs.ajax.data;
            configs.ajax.data = function (params) {
                SearchParams = {};
                $.each(configsData, function (key, selector) {
                    if ($(selector).is("select")) {
                        SearchParams[key] = $(selector)
                            .find(":selected")
                            .val();
                    } else {
                        SearchParams[key] = $(selector).val() ?? selector;
                    }
                });
                return Object.assign(SearchParams, {
                    search: params.term,
                });
            };
        }
        if (configs?.dropdownParent) {
            configs.dropdownParent = $(configs.dropdownParent);
        }
        $(this).select2(configs);
    });

    $(".form-group-item").each(function () {
        let container = $(this);

        $(this).on("click", ".btn-remove-item", function () {
            $(this).closest(".item").remove();
        });

        $(this).on("press", "input,select", function () {
            let value = $(this).val();

            $(this).attr("value", value);
        });
    });

    $(".form-group-item .btn-add-item").click(function () {
        var p = $(this).closest(".form-group-item").find(".g-items");

        let number = $(this)
            .closest(".form-group-item")
            .find(".g-items .item:last-child")
            .data("number");

        if (number === undefined) number = 0;
        else number++;

        let extra_html = $(this)
            .closest(".form-group-item")
            .find(".g-more")
            .html();

        extra_html = extra_html.replace(/__name__=/gi, "name=");

        extra_html = extra_html.replace(/__number__/gi, number);

        p.append(extra_html);

        if (extra_html.indexOf("dungdt-select2-field-lazy") > 0) {
            p.find(".dungdt-select2-field-lazy").each(function () {
                var configs = $(this).data("options");

                $(this).select2(configs);
            });
        }
    });

    $("table .check-all").change(function () {
        if ($(this).is(":checked")) {
            $(this)
                .closest("table")
                .find("tbody .check-item")
                .prop("checked", true);
        } else {
            $(this)
                .closest("table")
                .find("tbody .check-item")
                .prop("checked", false);
        }
    });

    $(".dungdt-apply-form-btn").click(function (e) {
        var $this = $(this);

        var action = $this.closest("form").find("[name=action]").val();

        var apply_action = function () {
            let ids = "";

            $(".bravo-form-item .check-item").each(function () {
                if ($(this).is(":checked")) {
                    ids +=
                        '<input type="hidden" name="ids[]" value="' +
                        $(this).val() +
                        '">';
                }
            });

            $this.closest("form").append(ids).submit();
        };
        apply_action();
    });
})(jQuery);
