$(function () {
    const ajaxPath = "../../src/Ajax.php";
    let debounce;

    $(".search input[type='search']").each(function () {
        const $input = $(this);
        // suppress the browser's own history dropdown so it doesn't overlap ours
        $input.attr("autocomplete", "off");
        const $label = $input.closest("label");
        $label.addClass("suggestWrapper");
        const $list = $("<ul class='searchSuggestions hide'></ul>");
        $label.append($list);
        let items = [];
        let active = -1;

        function close() {
            $list.addClass("hide").empty();
            items = [];
            active = -1;
        }

        function go(s) {
            let url = "/article?id=" + s.id;
            if (s.alias) {
                url += "&alias=" + encodeURIComponent(s.alias);
            }
            window.location = url;
        }

        function highlight(i) {
            const $lis = $list.children("li");
            $lis.removeClass("active");
            if (i >= 0 && i < $lis.length) {
                $lis.eq(i).addClass("active");
            }
            active = i;
        }

        function render(suggestions) {
            items = suggestions;
            active = -1;
            $list.empty();
            if (suggestions.length === 0) {
                close();
                return;
            }
            suggestions.forEach(function (s) {
                const $li = $("<li></li>");
                $("<span class='suggestTitle'></span>").text(s.headline).appendTo($li);
                if (s.alias) {
                    $("<span class='suggestAlias'></span>").text(s.alias).appendTo($li);
                }
                // mousedown fires before the input's blur, so the click still registers
                $li.on("mousedown", function (e) {
                    e.preventDefault();
                    go(s);
                });
                $list.append($li);
            });
            $list.removeClass("hide");
        }

        $input.on("input", function () {
            const q = $input.val().trim();
            clearTimeout(debounce);
            if (q.length < 2) {
                close();
                return;
            }
            debounce = setTimeout(function () {
                $.post(ajaxPath, {type: "suggest", query: q}, function (data) {
                    let suggestions;
                    try {
                        suggestions = typeof data === "string" ? JSON.parse(data) : data;
                    } catch (e) {
                        suggestions = [];
                    }
                    // drop stale responses if the field changed in the meantime
                    if ($input.val().trim() === q) {
                        render(suggestions || []);
                    }
                });
            }, 200);
        });

        $input.on("keydown", function (e) {
            if ($list.hasClass("hide")) {
                return;
            }
            if (e.key === "ArrowDown") {
                e.preventDefault();
                highlight(Math.min(active + 1, items.length - 1));
            } else if (e.key === "ArrowUp") {
                e.preventDefault();
                highlight(Math.max(active - 1, 0));
            } else if (e.key === "Enter") {
                if (active >= 0 && items[active]) {
                    e.preventDefault();
                    go(items[active]);
                }
            } else if (e.key === "Escape") {
                close();
            }
        });

        // delay so a suggestion's mousedown can run before the list is removed
        $input.on("blur", function () {
            setTimeout(close, 150);
        });
    });
});
