$(function() {
    $.nette.init();
});


// modal close button
$(function(){
    $(document).on('click', '.modal .close-button', function(){
        $(this).closest('.modal').modal('hide');
        return false;
    });
});

// multiselect
$(function() {
    onArrive('.select-multiple', function () {
        $(this).removeClass('select-multiple');
        $(this).multiselect();
    });
});


function onArrive(selector, callback) {
    $('body').arrive(selector, callback);
    $(selector).each(function(){ callback.call(this); });
}

$(function() {
    onArrive('[data-toggle="popover"]', function () {
        $(this).popover({placement : 'auto'});
    });
});


// setup calendar
$(document).ready(function() {
    var fnSetupDate = function(obj){
        $(obj).datetimepicker(
            {
                locale: 'cs',
                format: 'D.M.YYYY',
                keepInvalid: true
            }
        );
    };

    var fnSetupDateTime = function(obj){
        $(obj).datetimepicker(
            {
                locale: 'cs',
                format: 'D.M.YYYY H:m',
                keepInvalid: true
            }
        );
    };

    var fnSetupMonthSelect = function(obj){
        $(obj).datetimepicker({
            viewMode: 'months',
            locale: 'cs',
            format: 'MM/YYYY'
        });
    };

    var fnSetupYearSelect = function(obj){
        $(obj).datetimepicker({
            viewMode: 'years',
            locale: 'cs',
            format: 'YYYY'
        });
    };

    onArrive('.input-group.date, input.date', function() { fnSetupDate(this); });
    onArrive('.input-group.date-time, input.datetime', function() { fnSetupDateTime(this); });
    onArrive('.input-group.month, input.month', function() { fnSetupMonthSelect(this); });
    onArrive('.input-group.year, input.year', function() { fnSetupYearSelect(this); });

});


function replaceInputWithFancytree(inputEl, tsEl) {
    var currentValues = inputEl.val().split(' ');

    var tsEl = $(tsEl).removeClass('hidden');

    inputEl.attr('type', 'hidden');

    tsEl.find('.tree').each(function() {
        var treeEl = $(this);

        var setupFn = function() {
            var tree = $(treeEl).fancytree('getTree');
            var allItems;

            // set current input values
            tree.visit(function(node){
                if($.inArray(node.key, currentValues) > -1) {
                    node.setSelected(true);
                    node.visitParents(function(parent){ parent.setExpanded(true); });
                } else node.setSelected(false);
            });

            $(treeEl).bind('fancytreeselect', function (evt, data) {
                var checkedItems = data.tree.getSelectedNodes();
                var checkedIds = [];

                for(i in checkedItems) {
                    var checkedItem = checkedItems[i];
                    checkedIds.push(checkedItem.key);
                }
                $(inputEl).val(checkedIds.join(' '));
            });

        };

        try {
            $(tsEl).find('.tree').fancytree('getTree');
            setupFn();
        }
        catch(err) {
            $(tsEl).find('.tree').bind('fancytreeinit', function() { setupFn(); });
        }
    });

    tsEl.insertAfter(inputEl);
}

function replaceInputWithJqxTreeSelect(inputEl, tsTemplateEl) {
    var currentValues = inputEl.val().split(' ');

    var tsEl = $(tsTemplateEl).removeClass('hidden');

    inputEl.attr('type', 'hidden');

    tsEl.find('.tree').each(function() {

        var jqxTree = $(this);
        var allItems;

        // form-filling
        jqxTree.on('checkChange', function (event) {
            var checkedItems = $(event.owner.element).jqxTree('getCheckedItems');
            var checkedIds = [];

            for(i in checkedItems) {
                var checkedItem = checkedItems[i];
                checkedIds.push(checkedItem.value);

                // if parent element not checked or root, add it to the list
                // if(checkedItem.parentElement == null || !jqxTree.jqxTree('getItem', checkedItem.parentElement).checked) {
                //}
            }

            $(inputEl).val(checkedIds.join(' '));
        });


        var setupFn = function() {
            // set current input values
            jqxTree.jqxTree('uncheckAll');

            allItems = jqxTree.jqxTree('getItems');

            for (i in allItems) {
                var item = allItems[i];

                if ($.inArray('' + item.value, currentValues) > -1) {

                    jqxTree.jqxTree('checkItem', item.element, true);

                    if (item.parentId)
                        jqxTree.jqxTree('expandItem', item.element);
                }
            }
        };

        if(typeof jqxTree.jqxTree('getItems') == 'undefined') {
            jqxTree.on('initialized', setupFn);
        } else setupFn();
    });

    tsEl.insertAfter(inputEl);
}