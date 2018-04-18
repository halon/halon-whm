$("document").ready(function() {

    $("#successMessage .close, #errorMessage .close, #bulkMessage .close").click(function() {
        $(this).parent().hide();
    });
     
    var table = $("#domains").DataTable({
                destroy: true,
                pageLength: 10,
                lengthMenu: [
                    [ 10, 30, 50, 100 ],
                    [ '10', '30', '50', '100' ]
                ],
                serverSide: true,
                responsive: true,
                orderCellsTop: true,
                autoWidth: false,
                aaSorting: [[1,'asc']],
                ajax: {
                    url: "ajax.php?page=Domains&action=getDomains",
                    type: "GET",
                    dataType: "json",
                    dataSrc: function(ret) {
                        $("#toggleItems").data("status", "allSelected");
                        $("#toggleItems").trigger("click");
                        return ret.data.slice(parseInt(ret.start), parseInt(ret.start) + parseInt(ret.length));
                    },
                },
                columns: [
                    { data: 0, orderable: false, searchable: false},
                    { data: 1, orderable: true, searchable: true},
                    { data: 2, orderable: true, searchable: false},
                    { data: 3, orderable: false, searchable: false},
                ]
            });

        $('#domains tbody').on('click', "button[name='toggleProtection']", function () {
            var domain = $(this).data("domain");
            var username = $(this).data("username");
            var type = ($(this).data("type"));
            toggleProtection(domain, username, type);
        });
    
    $("#enableProtectionForAllDomains, #disableProtectionForAllDomains").click(function() {
        var selectedItems = getSelectedItems();
        var type = $(this).data("type");
        if(selectedItems.length > 0) {
            hideMessage("errorMessage");
            ajaxParser.currentPage = (ajaxParser.url).substring((ajaxParser.url).indexOf("page") + 5);
            ajaxParser.request("toggleBulkProtection", 
            {
                type: type,
                items: selectedItems
            }, 
            function(ret) {
                checkResponse(ret.result, type);
            });
        }
        else {
            displayMessage("errorMessage", "You have to choose some items in order to perform bulk action");
        }
    });
   
    
    $("#toggleItems").click(function() {
        if($(this).data("status") == "allUnselected") {
            $("input:checkbox[name='checkedItems']").each(function() {
                $(this).prop("checked", true);
            });
            $(this).text($(this).data("uncheckalltext"));
            $(this).data("status", "allSelected");
        }
        else {
            $("input:checkbox[name='checkedItems']").each(function() {
                $(this).prop("checked", false);
            });
            $(this).text($(this).data("checkalltext"));
            $(this).data("status", "allUnselected");
        }
    });     
    
    $('#domains tbody').on('click', "input:checkbox[name='checkedItems']", function () {
        var status = $(this).prop("checked");
        var theSameStatus = true;
        $("input:checkbox[name='checkedItems']").each(function() {
            if($(this).prop("checked") != status) {
                theSameStatus = false;
            }
        }); 
        if(theSameStatus) { 
            if(status) {
                $("#toggleItems").data("status", "allSelected");
                $("#toggleItems").text($("#toggleItems").data("uncheckalltext"));
            }
            else {
                $("#toggleItems").data("status", "allUnselected");
                $("#toggleItems").text($("#toggleItems").data("checkalltext"));
            }
        }
    });
    
    function getSelectedItems() {
        var selectedItems = [];
        $("input:checkbox[name='checkedItems']").each(function() {
            if($(this).prop("checked")) {
                selectedItems.push({username: $(this).data("username"), domain: $(this).data("domain")});
            }
        });
        return selectedItems;
    }
    
    function toggleProtection(domain, username, type) {
        ajaxParser.currentPage = (ajaxParser.url).substring((ajaxParser.url).indexOf("page") + 5);
        ajaxParser.request("toggleProtection", 
        {
            domain: domain,
            username: username,
            type: type
        }, 
        function(ret) {
            checkResponse(ret.result, type);
        });
    }
    
    function checkResponse(response, type) {
        var responseMessage = "";
        for(var i = 0; i <= response.length - 1; i++) {
            responseMessage += "<span style='display:block'><b>" + response[i].domain + ": </b>";
            var message = (response[i].result === true)?(type == "enable"?"Protection has been enabled":"Protection has been disabled"):response[i].result + "<br />";
            responseMessage += message + "</span>";
            if(response[i].result === true) {
                changeButtonContent(response[i].domain, type);
            }
        }
        if(response.length  > 1) {
            displayMessage("bulkMessage", responseMessage);
        }
        else {
            response[0].result === true?displayMessage("successMessage", responseMessage):displayMessage("errorMessage", responseMessage);
        }
    }
      
    function changeButtonContent(domain, type) {
        var button = $("button[data-domain='" + domain + "']");
        if(type == "enable") {
            var enableStatus = $("thead td[data-statusenabledvalue]").data("statusenabledvalue");
            button.parent().prev().html(enableStatus);
            button.data("type", "disable");
            button.text(button.data("disablecontent"));
            button.attr("class", "btn btn-danger");
        }
        else {
            var disableStatus = $("thead td[data-statusdisabledvalue]").data("statusdisabledvalue");
            button.parent().prev().html(disableStatus);
            button.data("type", "enable");
            button.text(button.data("enablecontent")); 
            button.attr("class", "btn btn-success");
        }
    }
    
    function displayMessage(elementId, responseMessage) {
        if(elementId != "successMessage") {
            $("#successMessage").hide();
        }
        if(elementId != "errorMessage") {
            $("#errorMessage").hide();
        }
        if(elementId != "bulkMessage") {
            $("#bulkMessage").hide();
        }
        $("#" + elementId + " span[class='content']").empty();
        $("#" + elementId + " span[class='content']").html(responseMessage);
        $("#" + elementId).show();
        $("html, body").animate({ scrollTop: 0 }, "slow");
    }
    
    function hideMessage(elementId) {
        $("#" + elementId).hide();
    }
});
