var pcgfPrivateCategoriesUserContainer = $('#pcgf-privatecategories-user-container');
var pcgfPrivateCategoriesAllowedUsers = $('#pcgf-privatecategories-allowed-users');
var pcgfPrivateCategoriesGroupContainer = $('#pcgf-privatecategories-group-container');
var pcgfPrivateCategoriesAllowedGroups = $('#pcgf-privatecategories-allowed-groups');
var pcgfPrivateCategoriesAddInput = $('#pcgf-privatecategories-add-viewer');
var pcgfPrivateCategoriesSuggestions = $('#pcgf-privatecategories-suggestions');
var pcgfPrivateCategoriesUserSuggestions = $('#pcgf-privatecategories-user-suggestions');
var pcgfPrivateCategoriesGroupSuggestions = $('#pcgf-privatecategories-group-suggestions');
var pcgfPrivateCategoriesDeleteButton = $('#pcgf-privatecategories-delete-button');
var pcgfPrivateCategoriesLastSearch = '';

pcgfPrivateCategoriesAddInput.on('input', function() {
    var searchValue = pcgfPrivateCategoriesAddInput.val();
    pcgfPrivateCategoriesLastSearch = searchValue;
    $.ajax({
        url: pcgfPrivateCategoriesSuggestionLink, type: 'POST', data: {
            search: searchValue,
            category: pcgfPrivateCategoriesCategory,
            topic: pcgfPrivateCategoriesTopic,
            owner: pcgfPrivateCategoriesOwner
        }, success: function(result) {
            if (result['search'] == pcgfPrivateCategoriesLastSearch) {
                var userSuggestions = '';
                var groupSuggestions = '';
                var i;
                if (result['users'] !== undefined) {
                    for (i = 0; i < result['users'].length; i++) {
                        userSuggestions += '<div class="pcgf-privatecategory-suggestion"><input type="hidden" value="' + result['users'][i][0] + '"/>' + result['users'][i][1] + '</div>';
                    }
                }
                if (result['groups'] !== undefined) {
                    for (i = 0; i < result['groups'].length; i++) {
                        groupSuggestions += '<div class="pcgf-privatecategory-suggestion"><input type="hidden" value="' + result['groups'][i][0] + '"/><span>' + result['groups'][i][1] + '</span></div>';
                    }
                }
                if (userSuggestions == '') {
                    pcgfPrivateCategoriesUserContainer.hide();
                } else {
                    pcgfPrivateCategoriesUserContainer.show();
                    pcgfPrivateCategoriesUserSuggestions.html(userSuggestions);
                }
                if (groupSuggestions == '') {
                    pcgfPrivateCategoriesGroupContainer.hide();
                } else {
                    pcgfPrivateCategoriesGroupContainer.show();
                    pcgfPrivateCategoriesGroupSuggestions.html(groupSuggestions);
                }
                if (userSuggestions.length > 0 || groupSuggestions.length > 0) {
                    pcgfPrivateCategoriesSuggestions.show();
                } else {
                    pcgfPrivateCategoriesSuggestions.hide();
                }
            }
        }
    });
});

pcgfPrivateCategoriesUserSuggestions.on('click', 'div', function() {
    pcgfPrivateCategoriesAdd(0, $(this));
});

pcgfPrivateCategoriesGroupSuggestions.on('click', 'div', function() {
    pcgfPrivateCategoriesAdd(1, $(this));
});

function pcgfPrivateCategoriesAdd(group, clickedElement) {
    $.ajax({
        url: pcgfPrivateCategoriesAddLink, type: 'POST', data: {
            is_group: group,
            viewer: clickedElement.find('input').val(),
            category: pcgfPrivateCategoriesCategory,
            topic: pcgfPrivateCategoriesTopic,
            owner: pcgfPrivateCategoriesOwner
        }, success: function(result) {
            if (group && result['type'] === 'group') {
                var allowedGroups = pcgfPrivateCategoriesAllowedGroups.html();
                pcgfPrivateCategoriesAllowedGroups.html(allowedGroups + (allowedGroups.length > 0 ? ',&nbsp;' : '') + result['viewer']);
                pcgfPrivateCategoriesAddInput.val('');
                pcgfPrivateCategoriesSuggestions.hide();
            } else if (!group && result['type'] === 'user') {
                var allowedUsers = pcgfPrivateCategoriesAllowedUsers.html();
                pcgfPrivateCategoriesAllowedUsers.html(allowedUsers + (allowedUsers.length > 0 ? ',&nbsp;' : '') + result['viewer']);
                pcgfPrivateCategoriesAddInput.val('');
                pcgfPrivateCategoriesSuggestions.hide();
            } else {
                alert(pcgfPrivateCategoriesAddError);
            }
        }
    });
}

function pcgfPrivateCategoriesDelete(event) {
    event.preventDefault();
    event.stopPropagation();
    if (confirm(pcgfPrivateCategoriesRemoveConfirmation)) {
        var element = $(this);
        var link = element.attr('href');
        var user;
        if (link === undefined) {
            user = element.attr('data');
        } else {
            user = pcgfPrivateCategoriesGetURLParameter(link, 'u');
        }
        var group;
        var viewer;
        if (user === undefined) {
            group = 1;
            viewer = pcgfPrivateCategoriesGetURLParameter(link, 'g');
        } else {
            group = 0;
            viewer = user;
        }
        $.ajax({
            url: pcgfPrivateCategoriesRemoveLink, type: 'POST', data: {
                is_group: group,
                viewer: viewer,
                category: pcgfPrivateCategoriesCategory,
                topic: pcgfPrivateCategoriesTopic,
                owner: pcgfPrivateCategoriesOwner
            }, success: function(result) {
                if (result[0] == true) {
                    element.remove();
                    var html = group > 0 ? pcgfPrivateCategoriesAllowedGroups.html() : pcgfPrivateCategoriesAllowedUsers.html();
                    html = html.replace(",&nbsp;,&nbsp;", ",&nbsp;");
                    if (html.indexOf(",&nbsp;") === 0) {
                        html = html.substring(7);
                    } else if (html.lastIndexOf(",&nbsp;") === html.length - 7) {
                        html = html.substring(0, html.length - 7);
                    }
                    if (group > 0) {
                        pcgfPrivateCategoriesAllowedGroups.html(html);
                    } else {
                        pcgfPrivateCategoriesAllowedUsers.html(html);
                    }
                }
            }
        });
    }
    pcgfPrivateCategoriesDeleteButton.trigger('click');
}

function pcgfPrivateCategoriesGetURLParameter(url, parameter) {
    url = decodeURIComponent(url);
    url = url.substring(url.indexOf('?') + 1);
    var variables = url.split('&');
    var single;
    for (var i = 0; i < variables.length; i++) {
        single = variables[i].split('=');
        if (single[0] === parameter) {
            return single[1];
        }
    }
}

pcgfPrivateCategoriesDeleteButton.on('click', function() {
    if (pcgfPrivateCategoriesDeleteButton.val() == pcgfPrivateCategoriesRemoveText) {
        pcgfPrivateCategoriesAllowedUsers.on('click', 'a', pcgfPrivateCategoriesDelete);
        pcgfPrivateCategoriesAllowedGroups.on('click', 'a', pcgfPrivateCategoriesDelete);
        pcgfPrivateCategoriesDeleteButton.val(pcgfPrivateCategoriesRemoveMessage);
    } else {
        pcgfPrivateCategoriesAllowedUsers.off('click');
        pcgfPrivateCategoriesAllowedGroups.off('click');
        pcgfPrivateCategoriesDeleteButton.val(pcgfPrivateCategoriesRemoveText);
    }
});