/**
 * @author zhixin wen <wenzhixin2010@gmail.com>
 * version: 1.8.1
 * https://github.com/wenzhixin/bootstrap-table/
 */
! function(t) {
    "use strict";
    var i = null,
        e = function(t) {
            var i = arguments,
                e = !0,
                o = 1;
            return t = t.replace(/%s/g, function() {
                var t = i[o++];
                return "undefined" == typeof t ? (e = !1, "") : t
            }), e ? t : ""
        },
        o = function(i, e, o, s) {
            var n = "";
            return t.each(i, function(t, i) {
                return i[e] === s ? (n = i[o], !1) : !0
            }), n
        },
        s = function(i, e) {
            var o = -1;
            return t.each(i, function(t, i) {
                return i.field === e ? (o = t, !1) : !0
            }), o
        },
        n = function(i) {
            var e, o, s, n = 0,
                a = [];
            for (e = 0; e < i[0].length; e++) {
                n += i[0][e].colspan || 1;
            }
            for (e = 0; e < i.length; e++) {
                for (a[e] = [], o = 0; n > o; o++) {
                    a[e][o] = !1;
                }
            }
            for (e = 0; e < i.length; e++) {
                for (o = 0; o < i[e].length; o++) {
                    var r = i[e][o],
                        h = r.rowspan || 1,
                        l = r.colspan || 1,
                        p = t.inArray(!1, a[e]);
                    for (1 === l && (r.fieldIndex = p, "undefined" == typeof r.field && (r.field = p)), s = 0; h > s; s++) {
                        a[e + s][p] = !0;
                    }
                    for (s = 0; l > s; s++) {
                        a[e][p + s] = !0;
                    }
                }
            }
        },
        a = function() {
            if (null === i) {
                var e, o, s = t("<p/>").addClass("fixed-table-scroll-inner"),
                    n = t("<div/>").addClass("fixed-table-scroll-outer");
                n.append(s), t("body").append(n), e = s[0].offsetWidth, n.css("overflow", "scroll"), o = s[0].offsetWidth, e === o && (o = n[0].clientWidth), n.remove(), i = e - o
            }
            return i
        },
        r = function(i, o, s, n) {
            var a = o;
            if ("string" == typeof o) {
                var r = o.split(".");
                r.length > 1 ? (a = window, t.each(r, function(t, i) {
                    a = a[i]
                })) : a = window[o]
            }
            return "object" == typeof a ? a : "function" == typeof a ? a.apply(i, s) : !a && "string" == typeof o && e.apply(this, [o].concat(s)) ? e.apply(this, [o].concat(s)) : n
        },
        h = function(i, e, o) {
            var s = Object.getOwnPropertyNames(i),
                n = Object.getOwnPropertyNames(e),
                a = "";
            if (o && s.length != n.length) {
                return !1;
            }
            for (var r = 0; r < s.length; r++) {
                if (a = s[r], t.inArray(a, n) > -1 && i[a] !== e[a]) {
                    return !1;
                }
            }
            return !0;
        },
        l = function(t) {
            return "string" == typeof t ? t.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : t
        },
        p = function(i) {
            var e = 0;
            return i.children().each(function() {
                e < t(this).outerHeight(!0) && (e = t(this).outerHeight(!0))
            }), e
        },
        c = function(t) {
            for (var i in t) {
                var e = i.split(/(?=[A-Z])/).join("-").toLowerCase();
                e !== i && (t[e] = t[i], delete t[i])
            }
            return t
        },
        d = function(i, e) {
            this.options = e, this.$el = t(i), this.$el_ = this.$el.clone(), this.timeoutId_ = 0, this.timeoutFooter_ = 0, this.init()
        };
    d.DEFAULTS = {
        classes: "table table-hover",
        locale: void 0,
        height: void 0,
        undefinedText: "-",
        sortName: void 0,
        sortOrder: "asc",
        striped: !1,
        columns: [
            []
        ],
        data: [],
        method: "get",
        url: void 0,
        ajax: void 0,
        cache: !0,
        contentType: "application/json",
        dataType: "json",
        ajaxOptions: {},
        queryParams: function(t) {
            return t
        },
        queryParamsType: "limit",
        responseHandler: function(t) {
            return t
        },
        pagination: !1,
        sidePagination: "client",
        totalRows: 0,
        pageNumber: 1,
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        paginationHAlign: "right",
        paginationVAlign: "bottom",
        paginationDetailHAlign: "left",
        paginationFirstText: "&laquo;",
        paginationPreText: "&lsaquo;",
        paginationNextText: "&rsaquo;",
        paginationLastText: "&raquo;",
        search: !1,
        strictSearch: !1,
        searchAlign: "right",
        selectItemName: "btSelectItem",
        showHeader: !0,
        showFooter: !1,
        showColumns: !1,
        showPaginationSwitch: !1,
        showRefresh: !1,
        showToggle: !1,
        buttonsAlign: "right",
        smartDisplay: !0,
        minimumCountColumns: 1,
        idField: void 0,
        uniqueId: void 0,
        cardView: !1,
        detailView: !1,
        detailFormatter: function() {
            return ""
        },
        trimOnSearch: !0,
        clickToSelect: !1,
        singleSelect: !1,
        toolbar: void 0,
        toolbarAlign: "left",
        checkboxHeader: !0,
        sortable: !0,
        maintainSelected: !1,
        searchTimeOut: 500,
        searchText: "",
        iconSize: void 0,
        iconsPrefix: "glyphicon",
        icons: {
            paginationSwitchDown: "glyphicon-collapse-down icon-chevron-down",
            paginationSwitchUp: "glyphicon-collapse-up icon-chevron-up",
            refresh: "glyphicon-refresh icon-refresh",
            toggle: "glyphicon-list-alt icon-list-alt",
            columns: "glyphicon-th icon-th",
            detailOpen: "glyphicon-plus icon-plus",
            detailClose: "glyphicon-minus icon-minus"
        },
        rowStyle: function() {
            return {}
        },
        rowAttributes: function() {
            return {}
        },
        onAll: function() {
            return !1
        },
        onClickCell: function() {
            return !1
        },
        onDblClickCell: function() {
            return !1
        },
        onClickRow: function() {
            return !1
        },
        onDblClickRow: function() {
            return !1
        },
        onSort: function() {
            return !1
        },
        onCheck: function() {
            return !1
        },
        onUncheck: function() {
            return !1
        },
        onCheckAll: function() {
            return !1
        },
        onUncheckAll: function() {
            return !1
        },
        onCheckSome: function() {
            return !1
        },
        onUncheckSome: function() {
            return !1
        },
        onLoadSuccess: function() {
            return !1
        },
        onLoadError: function() {
            return !1
        },
        onColumnSwitch: function() {
            return !1
        },
        onPageChange: function() {
            return !1
        },
        onSearch: function() {
            return !1
        },
        onToggle: function() {
            return !1
        },
        onPreBody: function() {
            return !1
        },
        onPostBody: function() {
            return !1
        },
        onPostHeader: function() {
            return !1
        },
        onExpandRow: function() {
            return !1
        },
        onCollapseRow: function() {
            return !1
        },
        onRefreshOptions: function() {
            return !1
        },
        onResetView: function() {
            return !1
        }
    }, d.LOCALES = [], d.LOCALES["en-US"] = d.LOCALES.en = {
        formatLoadingMessage: function() {
            return wait_message
        },
        formatRecordsPerPage: function(t) {
            return e("%s records per page", t)
        },
        formatShowingRows: function(t, i, o) {
            return e("Showing %s to %s of %s rows", t, i, o)
        },
        formatSearch: function() {
            return "Search"
        },
        formatNoMatches: function() {
            return "No matching records found"
        },
        formatPaginationSwitch: function() {
            return "Hide/Show pagination"
        },
        formatRefresh: function() {
            return "Refresh"
        },
        formatToggle: function() {
            return "Toggle"
        },
        formatColumns: function() {
            return "Columns"
        },
        formatAllRows: function() {
            return "All"
        }
    }, t.extend(d.DEFAULTS, d.LOCALES["en-US"]), d.COLUMN_DEFAULTS = {
        radio: !1,
        checkbox: !1,
        checkboxEnabled: !0,
        field: void 0,
        title: void 0,
        titleTooltip: void 0,
        "class": void 0,
        align: void 0,
        halign: void 0,
        falign: void 0,
        valign: void 0,
        width: void 0,
        sortable: !1,
        order: "asc",
        visible: !0,
        switchable: !0,
        clickToSelect: !0,
        formatter: void 0,
        footerFormatter: void 0,
        events: void 0,
        sorter: void 0,
        sortName: void 0,
        cellStyle: void 0,
        searchable: !0,
        cardVisible: !0
    }, d.EVENTS = {
        "all.bs.table": "onAll",
        "click-cell.bs.table": "onClickCell",
        "dbl-click-cell.bs.table": "onDblClickCell",
        "click-row.bs.table": "onClickRow",
        "dbl-click-row.bs.table": "onDblClickRow",
        "sort.bs.table": "onSort",
        "check.bs.table": "onCheck",
        "uncheck.bs.table": "onUncheck",
        "check-all.bs.table": "onCheckAll",
        "uncheck-all.bs.table": "onUncheckAll",
        "check-some.bs.table": "onCheckSome",
        "uncheck-some.bs.table": "onUncheckSome",
        "load-success.bs.table": "onLoadSuccess",
        "load-error.bs.table": "onLoadError",
        "column-switch.bs.table": "onColumnSwitch",
        "page-change.bs.table": "onPageChange",
        "search.bs.table": "onSearch",
        "toggle.bs.table": "onToggle",
        "pre-body.bs.table": "onPreBody",
        "post-body.bs.table": "onPostBody",
        "post-header.bs.table": "onPostHeader",
        "expand-row.bs.table": "onExpandRow",
        "collapse-row.bs.table": "onCollapseRow",
        "refresh-options.bs.table": "onRefreshOptions",
        "reset-view.bs.table": "onResetView"
    }, d.prototype.init = function() {
        this.initLocale(), this.initContainer(), this.initTable(), this.initHeader(), this.initData(), this.initFooter(), this.initToolbar(), this.initPagination(), this.initBody(), this.initServer()
    }, d.prototype.initLocale = function() {
        if (this.options.locale) {
            var i = this.options.locale.split(/-|_/);
            i[0].toLowerCase(), i[1] && i[1].toUpperCase(), t.fn.bootstrapTable.locales[this.options.locale] ? t.extend(this.options, t.fn.bootstrapTable.locales[this.options.locale]) : t.fn.bootstrapTable.locales[i.join("-")] ? t.extend(this.options, t.fn.bootstrapTable.locales[i.join("-")]) : t.fn.bootstrapTable.locales[i[0]] && t.extend(this.options, t.fn.bootstrapTable.locales[i[0]])
        }
    }, d.prototype.initContainer = function() {
        this.$container = t(['<div class="bootstrap-table">', '<div class="fixed-table-toolbar"></div>', "top" === this.options.paginationVAlign || "both" === this.options.paginationVAlign ? '<div class="fixed-table-pagination" style="clear: both;"></div>' : "", '<div class="fixed-table-container">', '<div class="fixed-table-header"><table></table></div>', '<div class="fixed-table-body">', '<div class="fixed-table-loading">', this.options.formatLoadingMessage(), "</div>", "</div>", '<div class="fixed-table-footer"><table><tr></tr></table></div>', "bottom" === this.options.paginationVAlign || "both" === this.options.paginationVAlign ? '<div class="fixed-table-pagination"></div>' : "", "</div>", "</div>"].join("")), this.$container.insertAfter(this.$el), this.$tableContainer = this.$container.find(".fixed-table-container"), this.$tableHeader = this.$container.find(".fixed-table-header"), this.$tableBody = this.$container.find(".fixed-table-body"), this.$tableLoading = this.$container.find(".fixed-table-loading"), this.$tableFooter = this.$container.find(".fixed-table-footer"), this.$toolbar = this.$container.find(".fixed-table-toolbar"), this.$pagination = this.$container.find(".fixed-table-pagination"), this.$tableBody.append(this.$el), this.$container.after('<div class="clearfix"></div>'), this.$el.addClass(this.options.classes), this.options.striped && this.$el.addClass("table-striped"), -1 !== t.inArray("table-no-bordered", this.options.classes.split(" ")) && this.$tableContainer.addClass("table-no-bordered")
    }, d.prototype.initTable = function() {
        var i = this,
            e = [],
            o = [];
        this.$header = this.$el.find("thead"), this.$header.length || (this.$header = t("<thead></thead>").appendTo(this.$el)), this.$header.find("tr").each(function() {
            var i = [];
            t(this).find("th").each(function() {
                i.push(t.extend({}, {
                    title: t(this).html(),
                    "class": t(this).attr("class"),
                    titleTooltip: t(this).attr("title"),
                    rowspan: t(this).attr("rowspan") ? +t(this).attr("rowspan") : void 0,
                    colspan: t(this).attr("colspan") ? +t(this).attr("colspan") : void 0
                }, t(this).data()))
            }), e.push(i)
        }), t.isArray(this.options.columns[0]) || (this.options.columns = [this.options.columns]), this.options.columns = t.extend(!0, [], e, this.options.columns), this.columns = [], n(this.options.columns), t.each(this.options.columns, function(e, o) {
            t.each(o, function(o, s) {
                s = t.extend({}, d.COLUMN_DEFAULTS, s), "undefined" != typeof s.fieldIndex && (i.columns[s.fieldIndex] = s), i.options.columns[e][o] = s
            })
        }), this.options.data.length || (this.$el.find("tbody tr").each(function() {
            var e = {};
            e._id = t(this).attr("id"), e._class = t(this).attr("class"), e._data = c(t(this).data()), t(this).find("td").each(function(o) {
                var s = i.columns[o].field;
                e[s] = t(this).html(), e["_" + s + "_id"] = t(this).attr("id"), e["_" + s + "_class"] = t(this).attr("class"), e["_" + s + "_rowspan"] = t(this).attr("rowspan"), e["_" + s + "_title"] = t(this).attr("title"), e["_" + s + "_data"] = c(t(this).data())
            }), o.push(e)
        }), this.options.data = o)
    }, d.prototype.initHeader = function() {
        var i = this,
            o = {},
            s = [];
        this.header = {
            fields: [],
            styles: [],
            classes: [],
            formatters: [],
            events: [],
            sorters: [],
            sortNames: [],
            cellStyles: [],
            clickToSelects: [],
            searchables: []
        }, t.each(this.options.columns, function(n, a) {
            s.push("<tr>"), 0 == n && !i.options.cardView && i.options.detailView && s.push(e('<th class="detail" rowspan="%s"><div class="fht-cell"></div></th>', i.options.columns.length)), t.each(a, function(t, n) {
                var a = "",
                    r = "",
                    h = "",
                    l = "",
                    p = e(' class="%s"', n["class"]),
                    c = (i.options.sortOrder || n.order, "px"),
                    d = n.width;
                if (void 0 === n.width || i.options.cardView || "string" == typeof n.width && -1 !== n.width.indexOf("%") && (c = "%"), n.width && "string" == typeof n.width && (d = n.width.replace("%", "").replace("px", "")), r = e("text-align: %s; ", n.halign ? n.halign : n.align), h = e("text-align: %s; ", n.align), l = e("vertical-align: %s; ", n.valign), l += e("width: %s%s; ", n.checkbox || n.radio ? 36 : d, c), "undefined" != typeof n.fieldIndex) {
                    if (i.header.fields[n.fieldIndex] = n.field, i.header.styles[n.fieldIndex] = h + l, i.header.classes[n.fieldIndex] = p, i.header.formatters[n.fieldIndex] = n.formatter, i.header.events[n.fieldIndex] = n.events, i.header.sorters[n.fieldIndex] = n.sorter, i.header.sortNames[n.fieldIndex] = n.sortName, i.header.cellStyles[n.fieldIndex] = n.cellStyle, i.header.clickToSelects[n.fieldIndex] = n.clickToSelect, i.header.searchables[n.fieldIndex] = n.searchable, !n.visible) {
                        return;
                    }
                    if (i.options.cardView && !n.cardVisible) {
                        return;
                    }
                    o[n.field] = n;
                }
                s.push("<th" + e(' title="%s"', n.titleTooltip), n.checkbox || n.radio ? e(' class="bs-checkbox %s"', n["class"] || "") : p, e(' style="%s"', r + l), e(' rowspan="%s"', n.rowspan), e(' colspan="%s"', n.colspan), e(' data-field="%s"', n.field), ">"), s.push(e('<div class="th-inner %s">', i.options.sortable && n.sortable ? "sortable both" : "")), a = n.title, n.checkbox && (!i.options.singleSelect && i.options.checkboxHeader && (a = '<input name="btSelectAll" type="checkbox" />'), i.header.stateField = n.field), n.radio && (a = "", i.header.stateField = n.field, i.options.singleSelect = !0), s.push(a), s.push("</div>"), s.push('<div class="fht-cell"></div>'), s.push("</div>"), s.push("</th>")
            }), s.push("</tr>")
        }), this.$header.html(s.join("")), this.$header.find("th[data-field]").each(function() {
            t(this).data(o[t(this).data("field")])
        }), this.$container.off("click", ".th-inner").on("click", ".th-inner", function(e) {
            i.options.sortable && t(this).parent().data().sortable && i.onSort(e)
        }), !this.options.showHeader || this.options.cardView ? (this.$header.hide(), this.$tableHeader.hide(), this.$tableLoading.css("top", 0)) : (this.$header.show(), this.$tableHeader.show(), this.$tableLoading.css("top", this.$header.outerHeight() + 1), this.getCaret()), this.$selectAll = this.$header.find('[name="btSelectAll"]'), this.$container.off("click", '[name="btSelectAll"]').on("click", '[name="btSelectAll"]', function() {
            var e = t(this).prop("checked");
            i[e ? "checkAll" : "uncheckAll"]()
        })
    }, d.prototype.initFooter = function() {
        !this.options.showFooter || this.options.cardView ? this.$tableFooter.hide() : this.$tableFooter.show()
    }, d.prototype.initData = function(t, i) {
        this.data = "append" === i ? this.data.concat(t) : "prepend" === i ? [].concat(t).concat(this.data) : t || this.options.data, this.options.data = "append" === i ? this.options.data.concat(t) : "prepend" === i ? [].concat(t).concat(this.options.data) : this.data, "server" !== this.options.sidePagination && this.initSort()
    }, d.prototype.initSort = function() {
        var i = this,
            e = this.options.sortName,
            o = "desc" === this.options.sortOrder ? -1 : 1,
            s = t.inArray(this.options.sortName, this.header.fields); - 1 !== s && this.data.sort(function(n, a) {
                i.header.sortNames[s] && (e = i.header.sortNames[s]);
                var h = n[e],
                    l = a[e],
                    p = r(i.header, i.header.sorters[s], [h, l]);
                return void 0 !== p ? o * p : ((void 0 === h || null === h) && (h = ""), (void 0 === l || null === l) && (l = ""), t.isNumeric(h) && t.isNumeric(l) ? (h = parseFloat(h), l = parseFloat(l), l > h ? -1 * o : o) : h === l ? 0 : ("string" != typeof h && (h = h.toString()), -1 === h.localeCompare(l) ? -1 * o : o))
            })
    }, d.prototype.onSort = function(i) {
        var e = t(i.currentTarget).parent(),
            o = this.$header.find("th").eq(e.index());
        return this.$header.add(this.$header_).find("span.order").remove(), this.options.sortName === e.data("field") ? this.options.sortOrder = "asc" === this.options.sortOrder ? "desc" : "asc" : (this.options.sortName = e.data("field"), this.options.sortOrder = "asc" === e.data("order") ? "desc" : "asc"), this.trigger("sort", this.options.sortName, this.options.sortOrder), e.add(o).data("order", this.options.sortOrder), this.getCaret(), "server" === this.options.sidePagination ? void this.initServer() : (this.initSort(), void this.initBody())
    }, d.prototype.initToolbar = function() {
        var i, o, n = this,
            a = [],
            h = 0,
            l = 0;
        this.$toolbar.html(""), "string" == typeof this.options.toolbar && t(e('<div class="bars pull-%s"></div>', this.options.toolbarAlign)).appendTo(this.$toolbar).append(t(this.options.toolbar)), a = [e('<div class="columns columns-%s btn-group pull-%s">', this.options.buttonsAlign, this.options.buttonsAlign)], "string" == typeof this.options.icons && (this.options.icons = r(null, this.options.icons)), this.options.showPaginationSwitch && a.push(e('<button class="btn btn-default" type="button" name="paginationSwitch" title="%s">', this.options.formatPaginationSwitch()), e('<i class="%s %s"></i>', this.options.iconsPrefix, this.options.icons.paginationSwitchDown), "</button>"), this.options.showRefresh && a.push(e('<button class="btn btn-default' + (void 0 === this.options.iconSize ? "" : " btn-" + this.options.iconSize) + '" type="button" name="refresh" title="%s">', this.options.formatRefresh()), e('<i class="%s %s"></i>', this.options.iconsPrefix, this.options.icons.refresh), "</button>"), this.options.showToggle && a.push(e('<button class="btn btn-default' + (void 0 === this.options.iconSize ? "" : " btn-" + this.options.iconSize) + '" type="button" name="toggle" title="%s">', this.options.formatToggle()), e('<i class="%s %s"></i>', this.options.iconsPrefix, this.options.icons.toggle), "</button>"), this.options.showColumns && (a.push(e('<div class="keep-open btn-group" title="%s">', this.options.formatColumns()), '<button type="button" class="btn btn-default' + (void 0 == this.options.iconSize ? "" : " btn-" + this.options.iconSize) + ' dropdown-toggle" data-toggle="dropdown">', e('<i class="%s %s"></i>', this.options.iconsPrefix, this.options.icons.columns), ' <span class="caret"></span>', "</button>", '<ul class="dropdown-menu" role="menu">'), t.each(this.columns, function(t, i) {
            if (!(i.radio || i.checkbox || n.options.cardView && !i.cardVisible)) {
                var o = i.visible ? ' checked="checked"' : "";
                i.switchable && (a.push(e('<li><label><input type="checkbox" data-field="%s" value="%s"%s> %s</label></li>', i.field, t, o, i.title)), l++)
            }
        }), a.push("</ul>", "</div>")), a.push("</div>"), (this.showToolbar || a.length > 2) && this.$toolbar.append(a.join("")), this.options.showPaginationSwitch && this.$toolbar.find('button[name="paginationSwitch"]').off("click").on("click", t.proxy(this.togglePagination, this)), this.options.showRefresh && this.$toolbar.find('button[name="refresh"]').off("click").on("click", t.proxy(this.refresh, this)), this.options.showToggle && this.$toolbar.find('button[name="toggle"]').off("click").on("click", function() {
            n.toggleView()
        }), this.options.showColumns && (i = this.$toolbar.find(".keep-open"), l <= this.options.minimumCountColumns && i.find("input").prop("disabled", !0), i.find("li").off("click").on("click", function(t) {
            t.stopImmediatePropagation()
        }), i.find("input").off("click").on("click", function() {
            var i = t(this);
            n.toggleColumn(s(n.columns, t(this).data("field")), i.prop("checked"), !1), n.trigger("column-switch", t(this).data("field"), i.prop("checked"))
        })), this.options.search && (a = [], a.push('<div class="pull-' + this.options.searchAlign + ' search">', e('<input class="form-control' + (void 0 === this.options.iconSize ? "" : " input-" + this.options.iconSize) + '" type="text" placeholder="%s">', this.options.formatSearch()), "</div>"), this.$toolbar.append(a.join("")), o = this.$toolbar.find(".search input"), o.off("keyup drop").on("keyup drop", function(t) {
            clearTimeout(h), h = setTimeout(function() {
                n.onSearch(t)
            }, n.options.searchTimeOut)
        }), "" !== this.options.searchText && (o.val(this.options.searchText), clearTimeout(h), h = setTimeout(function() {
            o.trigger("keyup")
        }, n.options.searchTimeOut)))
    }, d.prototype.onSearch = function(i) {
        var e = t.trim(t(i.currentTarget).val());
        this.options.trimOnSearch && t(i.currentTarget).val() !== e && t(i.currentTarget).val(e), e !== this.searchText && (this.searchText = e, this.options.pageNumber = 1, this.initSearch(), this.updatePagination(), this.trigger("search", e))
    }, d.prototype.initSearch = function() {
        var i = this;
        if ("server" !== this.options.sidePagination) {
            var e = this.searchText && this.searchText.toLowerCase(),
                o = t.isEmptyObject(this.filterColumns) ? null : this.filterColumns;
            this.data = o ? t.grep(this.options.data, function(t) {
                for (var i in o) {
                    if (t[i] !== o[i]) {
                        return !1;
                    }
                }
                return !0;
            }) : this.options.data, this.data = e ? t.grep(this.data, function(o, n) {
                for (var a in o) {
                    a = t.isNumeric(a) ? parseInt(a, 10) : a;
                    var h = o[a],
                        l = i.columns[s(i.columns, a)],
                        p = t.inArray(a, i.header.fields);
                    h = r(l, i.header.formatters[p], [h, o, n], h);
                    var c = t.inArray(a, i.header.fields);
                    if (-1 !== c && i.header.searchables[c] && ("string" == typeof h || "number" == typeof h)) {
                        if (i.options.strictSearch) {
                            if ((h + "").toLowerCase() === e) {
                                return !0;
                            }
                        } else if (-1 !== (h + "").toLowerCase().indexOf(e)) {
                            return !0;
                        }
                    }
                }
                return !1
            }) : this.data
        }
    }, d.prototype.initPagination = function() {
        if (!this.options.pagination) {
            return void this.$pagination.hide();
        }
        this.$pagination.show();
        var i, o, s, n, a, r, h, l, p, c = this,
            d = [],
            u = !1,
            f = this.getData();
        if ("server" !== this.options.sidePagination && (this.options.totalRows = f.length), this.totalPages = 0, this.options.totalRows) {
            if (this.options.pageSize === this.options.formatAllRows()) {
                this.options.pageSize = this.options.totalRows, u = !0;
            }
            else if (this.options.pageSize === this.options.totalRows) {
                var g = "string" == typeof this.options.pageList ? this.options.pageList.replace("[", "").replace("]", "").replace(/ /g, "").toLowerCase().split(",") : this.options.pageList;
                g.indexOf(this.options.formatAllRows().toLowerCase()) > -1 && (u = !0)
            }
            this.totalPages = ~~((this.options.totalRows - 1) / this.options.pageSize) + 1, this.options.totalPages = this.totalPages
        }
        this.totalPages > 0 && this.options.pageNumber > this.totalPages && (this.options.pageNumber = this.totalPages), this.pageFrom = (this.options.pageNumber - 1) * this.options.pageSize + 1, this.pageTo = this.options.pageNumber * this.options.pageSize, this.pageTo > this.options.totalRows && (this.pageTo = this.options.totalRows), d.push('<div class="pull-' + this.options.paginationDetailHAlign + ' pagination-detail">', '<span class="pagination-info">', this.options.formatShowingRows(this.pageFrom, this.pageTo, this.options.totalRows), "</span>"), d.push('<span class="page-list">');
        var b = [e('<span class="btn-group %s">', "top" === this.options.paginationVAlign || "both" === this.options.paginationVAlign ? "dropdown" : "dropup"), '<button type="button" class="btn btn-default ' + (void 0 === this.options.iconSize ? "" : " btn-" + this.options.iconSize) + ' dropdown-toggle" data-toggle="dropdown">', '<span class="page-size">', u ? this.options.formatAllRows() : this.options.pageSize, "</span>", ' <span class="caret"></span>', "</button>", '<ul class="dropdown-menu" role="menu">'],
            m = this.options.pageList;
        if ("string" == typeof this.options.pageList) {
            var y = this.options.pageList.replace("[", "").replace("]", "").replace(/ /g, "").split(",");
            m = [], t.each(y, function(t, i) {
                m.push(i.toUpperCase() === c.options.formatAllRows().toUpperCase() ? c.options.formatAllRows() : +i)
            })
        }
        for (t.each(m, function(t, i) {
            if (!c.options.smartDisplay || 0 === t || m[t - 1] <= c.options.totalRows) {
                var o;
                o = u ? i === c.options.formatAllRows() ? ' class="active"' : "" : i === c.options.pageSize ? ' class="active"' : "", b.push(e('<li%s><a href="javascript:void(0)">%s</a></li>', o, i))
            }
        }), b.push("</ul></span>"), d.push(this.options.formatRecordsPerPage(b.join(""))), d.push("</span>"), d.push("</div>", '<div class="pull-' + this.options.paginationHAlign + ' pagination">', '<ul class="pagination' + (void 0 === this.options.iconSize ? "" : " pagination-" + this.options.iconSize) + '">', '<li class="page-first"><a href="javascript:void(0)">' + this.options.paginationFirstText + "</a></li>", '<li class="page-pre"><a href="javascript:void(0)">' + this.options.paginationPreText + "</a></li>"), this.totalPages < 5 ? (o = 1, s = this.totalPages) : (o = this.options.pageNumber - 2, s = o + 4, 1 > o && (o = 1, s = 5), s > this.totalPages && (s = this.totalPages, o = s - 4)), i = o; s >= i; i++) {
            d.push('<li class="page-number' + (i === this.options.pageNumber ? " active" : "") + '">', '<a href="javascript:void(0)">', i, "</a>", "</li>");
        }
        d.push('<li class="page-next"><a href="javascript:void(0)">' + this.options.paginationNextText + "</a></li>", '<li class="page-last"><a href="javascript:void(0)">' + this.options.paginationLastText + "</a></li>", "</ul>", "</div>"), this.$pagination.html(d.join("")), n = this.$pagination.find(".page-list a"), a = this.$pagination.find(".page-first"), r = this.$pagination.find(".page-pre"), h = this.$pagination.find(".page-next"), l = this.$pagination.find(".page-last"), p = this.$pagination.find(".page-number"), this.options.pageNumber <= 1 && (a.addClass("disabled"), r.addClass("disabled")), this.options.pageNumber >= this.totalPages && (h.addClass("disabled"), l.addClass("disabled")), this.options.smartDisplay && (this.totalPages <= 1 && this.$pagination.find("div.pagination").hide(), (m.length < 2 || this.options.totalRows <= m[0]) && this.$pagination.find("span.page-list").hide(), this.$pagination[this.getData().length ? "show" : "hide"]()), u && (this.options.pageSize = this.options.formatAllRows()), n.off("click").on("click", t.proxy(this.onPageListChange, this)), a.off("click").on("click", t.proxy(this.onPageFirst, this)), r.off("click").on("click", t.proxy(this.onPagePre, this)), h.off("click").on("click", t.proxy(this.onPageNext, this)), l.off("click").on("click", t.proxy(this.onPageLast, this)), p.off("click").on("click", t.proxy(this.onPageNumber, this))
    }, d.prototype.updatePagination = function(i) {
        i && t(i.currentTarget).hasClass("disabled") || (this.options.maintainSelected || this.resetRows(), this.initPagination(), "server" === this.options.sidePagination ? this.initServer() : this.initBody(), this.trigger("page-change", this.options.pageNumber, this.options.pageSize))
    }, d.prototype.onPageListChange = function(i) {
        var e = t(i.currentTarget);
        e.parent().addClass("active").siblings().removeClass("active"), this.options.pageSize = e.text().toUpperCase() === this.options.formatAllRows().toUpperCase() ? this.options.formatAllRows() : +e.text(), this.$toolbar.find(".page-size").text(this.options.pageSize), this.updatePagination(i)
    }, d.prototype.onPageFirst = function(t) {
        this.options.pageNumber = 1, this.updatePagination(t)
    }, d.prototype.onPagePre = function(t) {
        this.options.pageNumber--, this.updatePagination(t)
    }, d.prototype.onPageNext = function(t) {
        this.options.pageNumber++, this.updatePagination(t)
    }, d.prototype.onPageLast = function(t) {
        this.options.pageNumber = this.totalPages, this.updatePagination(t)
    }, d.prototype.onPageNumber = function(i) {
        this.options.pageNumber !== +t(i.currentTarget).text() && (this.options.pageNumber = +t(i.currentTarget).text(), this.updatePagination(i))
    }, d.prototype.initBody = function(i) {
        var n = this,
            a = [],
            h = this.getData();
        this.trigger("pre-body", h), this.$body = this.$el.find("tbody"), this.$body.length || (this.$body = t("<tbody></tbody>").appendTo(this.$el)), this.options.pagination && "server" !== this.options.sidePagination || (this.pageFrom = 1, this.pageTo = h.length);
        for (var p = this.pageFrom - 1; p < this.pageTo; p++) {
            var c, d = h[p],
                u = {},
                f = [],
                g = "",
                b = {},
                m = [];
            if (u = r(this.options, this.options.rowStyle, [d, p], u), u && u.css) {
                for (c in u.css) {
                    f.push(c + ": " + u.css[c]);
                }
            }
            if (b = r(this.options, this.options.rowAttributes, [d, p], b)) {
                for (c in b) {
                    m.push(e('%s="%s"', c, l(b[c])));
                }
            }
            d._data && !t.isEmptyObject(d._data) && t.each(d._data, function(t, i) {
                "index" !== t && (g += e(' data-%s="%s"', t, i))
            }), a.push("<tr", e(" %s", m.join(" ")), e(' id="%s"', t.isArray(d) ? void 0 : d._id), e(' class="%s"', u.classes || (t.isArray(d) ? void 0 : d._class)), e(' data-index="%s"', p), e(' data-uniqueid="%s"', d[this.options.uniqueId]), e("%s", g), ">"), this.options.cardView && a.push(e('<td colspan="%s">', this.header.fields.length)), !this.options.cardView && this.options.detailView && a.push("<td>", '<a class="detail-icon" href="javascript:">', e('<i class="%s %s"></i>', this.options.iconsPrefix, this.options.icons.detailOpen), "</a>", "</td>"), t.each(this.header.fields, function(i, h) {
                var l = "",
                    c = d[h],
                    g = "",
                    b = {},
                    m = "",
                    y = n.header.classes[i],
                    v = "",
                    w = "",
                    x = "",
                    $ = n.columns[s(n.columns, h)];
                if ($.visible) {
                    if (u = e('style="%s"', f.concat(n.header.styles[i]).join("; ")), c = r($, n.header.formatters[i], [c, d, p], c), d["_" + h + "_id"] && (m = e(' id="%s"', d["_" + h + "_id"])), d["_" + h + "_class"] && (y = e(' class="%s"', d["_" + h + "_class"])), d["_" + h + "_rowspan"] && (w = e(' rowspan="%s"', d["_" + h + "_rowspan"])), d["_" + h + "_title"] && (x = e(' title="%s"', d["_" + h + "_title"])), b = r(n.header, n.header.cellStyles[i], [c, d, p], b), b.classes && (y = e(' class="%s"', b.classes)), b.css) {
                        var k = [];
                        for (var S in b.css) {
                            k.push(S + ": " + b.css[S]);
                        }
                        u = e('style="%s"', k.concat(n.header.styles[i]).join("; "))
                    }
                    d["_" + h + "_data"] && !t.isEmptyObject(d["_" + h + "_data"]) && t.each(d["_" + h + "_data"], function(t, i) {
                        "index" !== t && (v += e(' data-%s="%s"', t, i))
                    }), $.checkbox || $.radio ? (g = $.checkbox ? "checkbox" : g, g = $.radio ? "radio" : g, l = [n.options.cardView ? '<div class="card-view">' : '<td class="bs-checkbox">', "<input" + e(' data-index="%s"', p) + e(' name="%s"', n.options.selectItemName) + e(' type="%s"', g) + e(' value="%s"', d[n.options.idField]) + e(' checked="%s"', c === !0 || c && c.checked ? "checked" : void 0) + e(' disabled="%s"', !$.checkboxEnabled || c && c.disabled ? "disabled" : void 0) + " />", n.options.cardView ? "</div>" : "</td>"].join(""), d[n.header.stateField] = c === !0 || c && c.checked) : (c = "undefined" == typeof c || null === c ? n.options.undefinedText : c, l = n.options.cardView ? ['<div class="card-view">', n.options.showHeader ? e('<span class="title" %s>%s</span>', u, o(n.columns, "field", "title", h)) : "", e('<span class="value">%s</span>', c), "</div>"].join("") : [e("<td%s %s %s %s %s %s>", m, y, u, v, w, x), c, "</td>"].join(""), n.options.cardView && n.options.smartDisplay && "" === c && (l = "")), a.push(l)
                }
            }), this.options.cardView && a.push("</td>"), a.push("</tr>")
        }
        a.length || a.push('<tr class="no-records-found">', e('<td colspan="%s">%s</td>', this.$header.find("th").length, this.options.formatNoMatches()), "</tr>"), this.$body.html(a.join("")), i || this.scrollTo(0), this.$body.find("> tr[data-index] > td").off("click dblclick").on("click dblclick", function(i) {
            var o = t(this),
                s = o.parent(),
                a = n.data[s.data("index")],
                r = o[0].cellIndex,
                h = n.$header.find("th:eq(" + r + ")"),
                l = h.data("field"),
                p = a[l];
            if (n.trigger("click" === i.type ? "click-cell" : "dbl-click-cell", l, p, a, o), n.trigger("click" === i.type ? "click-row" : "dbl-click-row", a, s), "click" === i.type && n.options.clickToSelect && n.header.clickToSelects[s.children().index(t(this))]) {
                var c = s.find(e('[name="%s"]', n.options.selectItemName));
                c.length && c[0].click()
            }
        }), this.$body.find("> tr[data-index] > td > .detail-icon").off("click").on("click", function() {
            var i = t(this),
                o = i.parent().parent(),
                s = o.data("index"),
                a = h[s];
            o.next().is("tr.detail-view") ? (i.find("i").attr("class", e("%s %s", n.options.iconsPrefix, n.options.icons.detailOpen)), o.next().remove(), n.trigger("collapse-row", s, a)) : (i.find("i").attr("class", e("%s %s", n.options.iconsPrefix, n.options.icons.detailClose)), o.after(e('<tr class="detail-view"><td colspan="%s">%s</td></tr>', o.find("td").length, r(n.options, n.options.detailFormatter, [s, a], ""))), n.trigger("expand-row", s, a, o.next().find("td"))), n.resetView()
        }), this.$selectItem = this.$body.find(e('[name="%s"]', this.options.selectItemName)), this.$selectItem.off("click").on("click", function(i) {
            i.stopImmediatePropagation();
            var e = t(this).prop("checked"),
                o = n.data[t(this).data("index")];
            o[n.header.stateField] = e, n.options.singleSelect && (n.$selectItem.not(this).each(function() {
                n.data[t(this).data("index")][n.header.stateField] = !1
            }), n.$selectItem.filter(":checked").not(this).prop("checked", !1)), n.updateSelected(), n.trigger(e ? "check" : "uncheck", o)
        }), t.each(this.header.events, function(i, e) {
            if (e) {
                "string" == typeof e && (e = r(null, e));
                var o = n.header.fields[i],
                    s = t.inArray(o, n.getVisibleFields());
                n.options.detailView && !n.options.cardView && (s += 1);
                for (var a in e) {
                    n.$body.find("tr").each(function() {
                        var i = t(this),
                            r = i.find(n.options.cardView ? ".card-view" : "td").eq(s),
                            h = a.indexOf(" "),
                            l = a.substring(0, h),
                            p = a.substring(h + 1),
                            c = e[a];
                        r.find(p).off(l).on(l, function(t) {
                            var e = i.data("index"),
                                s = n.data[e],
                                a = s[o];
                            c.apply(this, [t, a, s, e])
                        })
                    })
                }
            }
        }), this.updateSelected(), this.resetView(), this.trigger("post-body")
    }, d.prototype.initServer = function(i, e) {
        var o, s = this,
            n = {},
            a = {
                pageSize: this.options.pageSize === this.options.formatAllRows() ? this.options.totalRows : this.options.pageSize,
                pageNumber: this.options.pageNumber,
                searchText: this.searchText,
                sortName: this.options.sortName,
                sortOrder: this.options.sortOrder
        };
        (this.options.url || this.options.ajax) && ("limit" === this.options.queryParamsType && (a = {
            search: a.searchText,
            sort: a.sortName,
            order: a.sortOrder
        }, this.options.pagination && (a.limit = this.options.pageSize === this.options.formatAllRows() ? this.options.totalRows : this.options.pageSize, a.offset = this.options.pageSize === this.options.formatAllRows() ? 0 : this.options.pageSize * (this.options.pageNumber - 1))), t.isEmptyObject(this.filterColumnsPartial) || (a.filter = JSON.stringify(this.filterColumnsPartial, null)), n = r(this.options, this.options.queryParams, [a], n), t.extend(n, e || {}), n !== !1 && (i || this.$tableLoading.show(), o = t.extend({}, r(null, this.options.ajaxOptions), {
            type: this.options.method,
            url: this.options.url,
            data: "application/json" === this.options.contentType && "post" === this.options.method ? JSON.stringify(n) : n,
            cache: this.options.cache,
            contentType: this.options.contentType,
            dataType: this.options.dataType,
            success: function(t) {
                t = r(s.options, s.options.responseHandler, [t], t), s.load(t), s.trigger("load-success", t)
            },
            error: function(t) {
                s.trigger("load-error", t.status)
            },
            complete: function() {
                i || s.$tableLoading.hide()
            }
        }), this.options.ajax ? r(this, this.options.ajax, [o], null) : t.ajax(o)))
    }, d.prototype.getCaret = function() {
        var i = this;
        t.each(this.$header.find("th"), function(e, o) {
            t(o).find(".sortable").removeClass("desc asc").addClass(t(o).data("field") === i.options.sortName ? i.options.sortOrder : "both")
        })
    }, d.prototype.updateSelected = function() {
        var i = this.$selectItem.filter(":enabled").length === this.$selectItem.filter(":enabled").filter(":checked").length;
        this.$selectAll.add(this.$selectAll_).prop("checked", i), this.$selectItem.each(function() {
            t(this).parents("tr")[t(this).prop("checked") ? "addClass" : "removeClass"]("selected")
        })
    }, d.prototype.updateRows = function() {
        var i = this;
        this.$selectItem.each(function() {
            i.data[t(this).data("index")][i.header.stateField] = t(this).prop("checked")
        })
    }, d.prototype.resetRows = function() {
        var i = this;
        t.each(this.data, function(t, e) {
            i.$selectAll.prop("checked", !1), i.$selectItem.prop("checked", !1), e[i.header.stateField] = !1
        })
    }, d.prototype.trigger = function(i) {
        var e = Array.prototype.slice.call(arguments, 1);
        i += ".bs.table", this.options[d.EVENTS[i]].apply(this.options, e), this.$el.trigger(t.Event(i), e), this.options.onAll(i, e), this.$el.trigger(t.Event("all.bs.table"), [i, e])
    }, d.prototype.resetHeader = function() {
        clearTimeout(this.timeoutId_), this.timeoutId_ = setTimeout(t.proxy(this.fitHeader, this), this.$el.is(":hidden") ? 100 : 0)
    }, d.prototype.fitHeader = function() {
        var i, o, s = this;
        if (s.$el.is(":hidden")) {
            return void(s.timeoutId_ = setTimeout(t.proxy(s.fitHeader, s), 100));
        }
        i = this.$tableBody.get(0), o = i.scrollWidth > i.clientWidth && i.scrollHeight > i.clientHeight + this.$header.outerHeight() ? a() : 0, this.$el.css("margin-top", -this.$header.outerHeight()), this.$header_ = this.$header.clone(!0, !0), this.$selectAll_ = this.$header_.find('[name="btSelectAll"]'), this.$tableHeader.css({
            "margin-right": o
        }).find("table").css("width", this.$el.outerWidth()).html("").attr("class", this.$el.attr("class")).append(this.$header_), this.$header.find("th[data-field]").each(function() {
            s.$header_.find(e('th[data-field="%s"]', t(this).data("field"))).data(t(this).data())
        });
        var n = this.getVisibleFields();
        this.$body.find("tr:first-child:not(.no-records-found) > *").each(function(i) {
            var o = t(this),
                a = i;
            s.options.detailView && !s.options.cardView && (0 === i && s.$header_.find("th.detail").find(".fht-cell").width(o.innerWidth()), a = i - 1), s.$header_.find(e('th[data-field="%s"]', n[a])).find(".fht-cell").width(o.innerWidth())
        }), this.$tableBody.off("scroll").on("scroll", function() {
            s.$tableHeader.scrollLeft(t(this).scrollLeft())
        }), s.trigger("post-header")
    }, d.prototype.resetFooter = function() {
        var i = this,
            o = i.getData(),
            s = [];
        this.options.showFooter && !this.options.cardView && (!this.options.cardView && this.options.detailView && s.push("<td></td>"), t.each(this.columns, function(t, n) {
            var a = "",
                h = "",
                l = e(' class="%s"', n["class"]);
            n.visible && (!i.options.cardView || n.cardVisible) && (a = e("text-align: %s; ", n.falign ? n.falign : n.align), h = e("vertical-align: %s; ", n.valign), s.push("<td", l, e(' style="%s"', a + h), ">"), s.push(r(n, n.footerFormatter, [o], "&nbsp;") || "&nbsp;"), s.push("</td>"))
        }), this.$tableFooter.find("tr").html(s.join("")), clearTimeout(this.timeoutFooter_), this.timeoutFooter_ = setTimeout(t.proxy(this.fitFooter, this), this.$el.is(":hidden") ? 100 : 0))
    }, d.prototype.fitFooter = function() {
        var i, e, o;
        return clearTimeout(this.timeoutFooter_), this.$el.is(":hidden") ? void(this.timeoutFooter_ = setTimeout(t.proxy(this.fitFooter, this), 100)) : (e = this.$el.css("width"), o = e > this.$tableBody.width() ? a() : 0, this.$tableFooter.css({
            "margin-right": o
        }).find("table").css("width", e).attr("class", this.$el.attr("class")), i = this.$tableFooter.find("td"), void this.$tableBody.find("tbody tr:first-child:not(.no-records-found) > td").each(function(e) {
            i.eq(e).outerWidth(t(this).outerWidth())
        }))
    }, d.prototype.toggleColumn = function(t, i, o) {
        if (-1 !== t && (this.columns[t].visible = i, this.initHeader(), this.initSearch(), this.initPagination(), this.initBody(), this.options.showColumns)) {
            var s = this.$toolbar.find(".keep-open input").prop("disabled", !1);
            o && s.filter(e('[value="%s"]', t)).prop("checked", i), s.filter(":checked").length <= this.options.minimumCountColumns && s.filter(":checked").prop("disabled", !0)
        }
    }, d.prototype.toggleRow = function(i, o, s) {
        i !== -1 && t(this.$body[0]).children().filter(e(o ? '[data-uniqueid="%s"]' : '[data-index="%s"]', i))[s ? "show" : "hide"]()
    }, d.prototype.getVisibleFields = function() {
        var i = this,
            e = [];
        return t.each(this.header.fields, function(t, o) {
            var n = i.columns[s(i.columns, o)];
            n.visible && e.push(o)
        }), e
    }, d.prototype.resetView = function(t) {
        var i = 0;
        if (t && t.height && (this.options.height = t.height), this.$selectAll.prop("checked", this.$selectItem.length > 0 && this.$selectItem.length === this.$selectItem.filter(":checked").length), this.options.height) {
            var e = p(this.$toolbar),
                o = p(this.$pagination),
                s = this.options.height - e - o;
            this.$tableContainer.css("height", s + "px")
        }
        return this.options.cardView ? (this.$el.css("margin-top", "0"), void this.$tableContainer.css("padding-bottom", "0")) : (this.options.showHeader && this.options.height ? (this.$tableHeader.show(), this.resetHeader(), i += this.$header.outerHeight()) : (this.$tableHeader.hide(), this.trigger("post-header")), this.options.showFooter && (this.resetFooter(), this.options.height && (i += this.$tableFooter.outerHeight())), this.getCaret(), this.$tableContainer.css("padding-bottom", i + "px"), void this.trigger("reset-view"))
    }, d.prototype.getData = function(i) {
        return !this.searchText && t.isEmptyObject(this.filterColumns) && t.isEmptyObject(this.filterColumnsPartial) ? i ? this.options.data.slice(this.pageFrom - 1, this.pageTo) : this.options.data : i ? this.data.slice(this.pageFrom - 1, this.pageTo) : this.data
    }, d.prototype.load = function(i) {
        var e = !1;
        "server" === this.options.sidePagination ? (this.options.totalRows = i.total, e = i.fixedScroll, i = i.rows) : t.isArray(i) || (e = i.fixedScroll, i = i.data), this.initData(i), this.initSearch(), this.initPagination(), this.initBody(e)
    }, d.prototype.append = function(t) {
        this.initData(t, "append"), this.initSearch(), this.initPagination(), this.initBody(!0)
    }, d.prototype.prepend = function(t) {
        this.initData(t, "prepend"), this.initSearch(), this.initPagination(), this.initBody(!0)
    }, d.prototype.remove = function(i) {
        var e, o, s = this.options.data.length;
        if (i.hasOwnProperty("field") && i.hasOwnProperty("values")) {
            for (e = s - 1; e >= 0; e--) {
                o = this.options.data[e], o.hasOwnProperty(i.field) && -1 !== t.inArray(o[i.field], i.values) && this.options.data.splice(e, 1);
            }
            s !== this.options.data.length && (this.initSearch(), this.initPagination(), this.initBody(!0))
        }
    }, d.prototype.removeAll = function() {
        this.options.data.length > 0 && (this.options.data.splice(0, this.options.data.length), this.initSearch(), this.initPagination(), this.initBody(!0))
    }, d.prototype.getRowByUniqueId = function(t) {
        var i, e, o = this.options.uniqueId,
            s = this.options.data.length,
            n = void 0;
        for (i = s - 1; i >= 0; i--) {
            if (e = this.options.data[i], e.hasOwnProperty(o) && ("string" == typeof e[o] ? t = t.toString() : "number" == typeof e[o] && (Number(e[o]) === e[o] && e[o] % 1 === 0 ? t = parseInt(t) : e[o] === Number(e[o]) && 0 !== e[o] && (t = parseFloat(t))), e[o] === t)) {
                n = e;
                break
            }
        }
        return n;
    }, d.prototype.removeByUniqueId = function(t) {
        var i = this.options.data.length,
            e = this.getRowByUniqueId(t);
        e && this.options.data.splice(this.options.data.indexOf(e), 1), i !== this.options.data.length && (this.initSearch(), this.initPagination(), this.initBody(!0))
    }, d.prototype.insertRow = function(t) {
        t.hasOwnProperty("index") && t.hasOwnProperty("row") && (this.data.splice(t.index, 0, t.row), this.initSearch(), this.initPagination(), this.initSort(), this.initBody(!0))
    }, d.prototype.updateRow = function(i) {
        i.hasOwnProperty("index") && i.hasOwnProperty("row") && (t.extend(this.data[i.index], i.row), this.initSort(), this.initBody(!0))
    }, d.prototype.showRow = function(t) {
        t.hasOwnProperty("index") && this.toggleRow(t.index, void 0 === t.isIdField ? !1 : !0, !0)
    }, d.prototype.hideRow = function(t) {
        t.hasOwnProperty("index") && this.toggleRow(t.index, void 0 === t.isIdField ? !1 : !0, !1)
    }, d.prototype.getRowsHidden = function(i) {
        var e = t(this.$body[0]).children().filter(":hidden"),
            o = 0;
        if (i) {
            for (; o < e.length; o++) {
                t(e[o]).show();
            }
        }
        return e
    }, d.prototype.mergeCells = function(i) {
        var e, o, s, n = i.index,
            a = t.inArray(i.field, this.getVisibleFields()),
            r = i.rowspan || 1,
            h = i.colspan || 1,
            l = this.$body.find("tr");
        if (this.options.detailView && !this.options.cardView && (a += 1), s = l.eq(n).find("td").eq(a), !(0 > n || 0 > a || n >= this.data.length)) {
            for (e = n; n + r > e; e++) {
                for (o = a; a + h > o; o++) {
                    l.eq(e).find("td").eq(o).hide();
                }
            }
            s.attr("rowspan", r).attr("colspan", h).show()
        }
    }, d.prototype.updateCell = function(t) {
        t.hasOwnProperty("rowIndex") && t.hasOwnProperty("fieldName") && t.hasOwnProperty("fieldValue") && (this.data[t.rowIndex][t.fieldName] = t.fieldValue, this.initSort(), this.initBody(!0))
    }, d.prototype.getOptions = function() {
        return this.options
    }, d.prototype.getSelections = function() {
        var i = this;
        return t.grep(this.data, function(t) {
            return t[i.header.stateField]
        })
    }, d.prototype.getAllSelections = function() {
        var i = this;
        return t.grep(this.options.data, function(t) {
            return t[i.header.stateField]
        })
    }, d.prototype.checkAll = function() {
        this.checkAll_(!0)
    }, d.prototype.uncheckAll = function() {
        this.checkAll_(!1)
    }, d.prototype.checkAll_ = function(t) {
        var i;
        t || (i = this.getSelections()), this.$selectItem.filter(":enabled").prop("checked", t), this.updateRows(), this.updateSelected(), t && (i = this.getSelections()), this.trigger(t ? "check-all" : "uncheck-all", i)
    }, d.prototype.check = function(t) {
        this.check_(!0, t)
    }, d.prototype.uncheck = function(t) {
        this.check_(!1, t)
    }, d.prototype.check_ = function(t, i) {
        this.$selectItem.filter(e('[data-index="%s"]', i)).prop("checked", t), this.data[i][this.header.stateField] = t, this.updateSelected(), this.trigger(t ? "check" : "uncheck", this.data[i])
    }, d.prototype.checkBy = function(t) {
        this.checkBy_(!0, t)
    }, d.prototype.uncheckBy = function(t) {
        this.checkBy_(!1, t)
    }, d.prototype.checkBy_ = function(i, o) {
        if (o.hasOwnProperty("field") && o.hasOwnProperty("values")) {
            var s = this,
                n = [];
            t.each(this.options.data, function(a, r) {
                return r.hasOwnProperty(o.field) ? void(-1 !== t.inArray(r[o.field], o.values) && (s.$selectItem.filter(e('[data-index="%s"]', a)).prop("checked", i), r[s.header.stateField] = i, n.push(r), s.trigger(i ? "check" : "uncheck", r))) : !1
            }), this.updateSelected(), this.trigger(i ? "check-some" : "uncheck-some", n)
        }
    }, d.prototype.destroy = function() {
        this.$el.insertBefore(this.$container), t(this.options.toolbar).insertBefore(this.$el), this.$container.next().remove(), this.$container.remove(), this.$el.html(this.$el_.html()).css("margin-top", "0").attr("class", this.$el_.attr("class") || "")
    }, d.prototype.showLoading = function() {
        this.$tableLoading.show()
    }, d.prototype.hideLoading = function() {
        this.$tableLoading.hide()
    }, d.prototype.togglePagination = function() {
        this.options.pagination = !this.options.pagination;
        var t = this.$toolbar.find('button[name="paginationSwitch"] i');
        this.options.pagination ? t.attr("class", this.options.iconsPrefix + " " + this.options.icons.paginationSwitchDown) : t.attr("class", this.options.iconsPrefix + " " + this.options.icons.paginationSwitchUp), this.updatePagination()
    }, d.prototype.refresh = function(t) {
        t && t.url && (this.options.url = t.url, this.options.pageNumber = 1), this.initServer(t && t.silent, t && t.query)
    }, d.prototype.resetWidth = function() {
        this.options.showHeader && this.options.height && this.fitHeader(), this.options.showFooter && this.fitFooter()
    }, d.prototype.showColumn = function(t) {
        this.toggleColumn(s(this.columns, t), !0, !0)
    }, d.prototype.hideColumn = function(t) {
        this.toggleColumn(s(this.columns, t), !1, !0)
    }, d.prototype.getHiddenColumns = function() {
        return t.grep(this.columns, function(t) {
            return !t.visible
        })
    }, d.prototype.filterBy = function(i) {
        this.filterColumns = t.isEmptyObject(i) ? {} : i, this.options.pageNumber = 1, this.initSearch(), this.updatePagination()
    }, d.prototype.scrollTo = function(t) {
        return "string" == typeof t && (t = "bottom" === t ? this.$tableBody[0].scrollHeight : 0), "number" == typeof t && this.$tableBody.scrollTop(t), "undefined" == typeof t ? this.$tableBody.scrollTop() : void 0
    }, d.prototype.getScrollPosition = function() {
        return this.scrollTo()
    }, d.prototype.selectPage = function(t) {
        t > 0 && t <= this.options.totalPages && (this.options.pageNumber = t, this.updatePagination())
    }, d.prototype.prevPage = function() {
        this.options.pageNumber > 1 && (this.options.pageNumber--, this.updatePagination())
    }, d.prototype.nextPage = function() {
        this.options.pageNumber < this.options.totalPages && (this.options.pageNumber++, this.updatePagination())
    }, d.prototype.toggleView = function() {
        this.options.cardView = !this.options.cardView, this.initHeader(), this.initBody(), this.trigger("toggle", this.options.cardView)
    }, d.prototype.refreshOptions = function(i) {
        h(this.options, i, !1) || (this.options = t.extend(this.options, i), this.trigger("refresh-options", this.options), this.destroy(), this.init())
    };
    var u = ["getOptions", "getSelections", "getAllSelections", "getData", "load", "append", "prepend", "remove", "removeAll", "insertRow", "updateRow", "updateCell", "removeByUniqueId", "getRowByUniqueId", "showRow", "hideRow", "getRowsHidden", "mergeCells", "checkAll", "uncheckAll", "check", "uncheck", "checkBy", "uncheckBy", "refresh", "resetView", "resetWidth", "destroy", "showLoading", "hideLoading", "showColumn", "hideColumn", "getHiddenColumns", "filterBy", "scrollTo", "getScrollPosition", "selectPage", "prevPage", "nextPage", "togglePagination", "toggleView", "refreshOptions"];
    t.fn.bootstrapTable = function(i) {
        var e, o = Array.prototype.slice.call(arguments, 1);
        return this.each(function() {
            var s = t(this),
                n = s.data("bootstrap.table"),
                a = t.extend({}, d.DEFAULTS, s.data(), "object" == typeof i && i);
            if ("string" == typeof i) {
                if (t.inArray(i, u) < 0) {
                    throw new Error("Unknown method: " + i);
                }
                if (!n) {
                    return;
                }
                e = n[i].apply(n, o), "destroy" === i && s.removeData("bootstrap.table")
            }
            n || s.data("bootstrap.table", n = new d(this, a))
        }), "undefined" == typeof e ? this : e
    }, t.fn.bootstrapTable.Constructor = d, t.fn.bootstrapTable.defaults = d.DEFAULTS, t.fn.bootstrapTable.columnDefaults = d.COLUMN_DEFAULTS, t.fn.bootstrapTable.locales = d.LOCALES, t.fn.bootstrapTable.methods = u, t(function() {
        t('[data-toggle="table"]').bootstrapTable()
    })
}(jQuery);