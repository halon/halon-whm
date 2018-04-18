var ajaxParser = {
    url:            false,
    type:           'post',
    startString:    '<!--JSONRESPONSE#',
    stopString:     '#ENDJSONRESPONSE -->',
    startException: '<!--EXCEPTION#',
    stopException:  '#ENDEXCEPTION -->',
    currentPage:    false,
    enableDebug:    false,
    requestCounter: 0,
    
    create: function(url,type){
        this.url = url;
        if(type !== undefined)
        {
            this.type = type;
        }
    },
    debug: function(json)
    {
        var exStart = json.indexOf(this.startException);
        if(exStart !== -1)
        {        
            exception = json.substr(exStart+this.startException.length,json.indexOf(this.stopException)-exStart-this.startException.length);
            console.log("EXCEPTION");
            console.log(jQuery.parseJSON(exception));
            console.log("END OF EXCEPTION");
        }
    },
    getJSON: function(json,disableError){
            if(this.enableDebug)
            {
                this.debug(json);
            }
            
            this.requestCounter--;
            if(this.requestCounter == 0)
            {
                jQuery('#MGLoader').loader('hide');
            }
            
            var start = json.indexOf(this.startString);
            json = json.substr(start+this.startString.length,json.indexOf(this.stopString)-start-this.startString.length);
                               
            try{
                var response = jQuery.parseJSON(json);

                if(response.result && response.result == 'error' && disableError === undefined)
                {
                    var config = {};
                    
                    if(response.errorID)
                    {
                        config.errorID = response.errorID;
                    }

                    jQuery('#MGErrors').alerts('danger',response.error,config);
                }
                return response;
            }catch(e)
            {
                jQuery('#MGErrors').alerts('danger',"Somethings Goes Wrong, check logs, contact admin");
                return false;
            }
    },
    rawDataRequest: function(action, data, fields,callback, loader, disableErrors)
    {
        var that = this;
        
        if(fields === undefined)
        {
            fields = {};
        }

        data.append('action',action);
        data.append('page',that.currentPage);
        
        jQuery.each(fields, function(key, value) {
            data.append('data['+key+']', value);
        });

        if(loader === undefined)
        {
            jQuery('#MGLoader').loader();
        }
        
        this.requestCounter++;

        switch(this.type)
        {
            default:
                jQuery.ajax({
                    type: 'post',
                    url: that.url+'&action='+action,
                    data: data,
                    success: function (response) {
                        parsed = that.getJSON(response,disableErrors);
                        if(parsed !== false)
                        {
                            if(callback === undefined)
                            {
                                console.log(parsed);
                            }
                            else
                            {
                                if(parsed.success)
                                {
                                    callback(parsed.success);
                                }
                                else
                                {
                                    if(parsed.errors)
                                    {
                                        callback({error:parsed.errors.join()});
                                    }
                                }
                            }
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                }).fail(function(response) {
                    jQuery('#MGErrors').alerts('danger',response.responseText);
                    jQuery('#MGLoader').loader('hide');
                });
        }
    },
    request: function (action, data, callback, loader, disableErrors) {
        var details = {};
        var that = this;
       
        if(data === undefined)
        {
            data = {};
        }
       
        details.page    = this.currentPage;
        details.action  = action;
        details.data    = jQuery.extend({}, data);

        if(loader === undefined)
        {
            jQuery('#MGLoader').loader();
        }
                
        this.requestCounter++;

        switch(this.type)
        {
            default:
                jQuery.post(this.url+'&action='+action,details,function(response){
                    parsed = that.getJSON(response,disableErrors);
                    if(parsed !== false)
                    {
                        if(callback === undefined)
                        {
                            console.log(parsed);
                        }
                        else
                        {
                            if(parsed.success)
                            {
                                callback(parsed.success);
                            }
                            else
                            {
                                if(parsed.errors)
                                {
                                    callback({error:parsed.errors.join()});
                                }
                            }
                        }
                    }
                }).fail(function(response) {
                    jQuery('#MGErrors').alerts('danger',response.responseText);
                    jQuery('#MGLoader').loader('hide');
                });
        }
    }
};


function ucfirst(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

jQuery.fn.alerts = function(type,message,configs){
        
    configs = jQuery.extend({
        items: null
        ,confirmCallback: null
        ,error: null
        ,errorID: null
    }, configs);
    

    items           = configs.items;
    confirmCallback = configs.confirmCallback;
    error           = configs.error;
    
    
    var container = this.find('.alertContainer');
    
    var now = new Date().getTime();
    
    var current = new Array();
    
    var count = 0;
    
    var max = 2;
    
    jQuery(container).children('div[class*="alert-"]').each(function(){
        var time = new String(jQuery(this).attr('data-time'));
        current[time] = 1;
        count++;
    });
    
    current.sort();
        
    if(count > max)
    {
        for(x in current)
        {
            var set = parseInt(x);
            if(set > 0)
            {
                if( now-set > 10 && count-max > 0)
                {
                    jQuery(container).find('div[data-time="'+set+'"]').remove();
                    count--;
                }
            }
        }
    }
        
    if(type === 'clear')
    {
        jQuery(container).children('div[class*="alert-"]').remove();
        return;
    }
        
    var prototype = jQuery(container).find('.alertPrototype .alert-'+type).clone();

    prototype.find('strong').append(message);

    if(items != undefined)
    {
        var html = '<ul>';
        for(x in items)
        {
            html += '<li>'+items[x]+'</li>';
        }
        html += '</ul>';
        prototype.append(html);
    }   
    
    prototype.find('.close').click(function(){
       jQuery(this).parent().remove(); 
    });
    
    prototype.attr('data-time',now);
    
    if(configs.errorID)
    {
        prototype.find('.errorID').attr('href','index.php?page=ErrorList&action=SearchToken&tokenID='+configs.errorID).text(configs.errorID).show();
    }
        
    jQuery(container).append(prototype);
};

jQuery.fn.loader = function(action)
{
    if(action === undefined || action == 'show')
    {
        jQuery(this).show();
    }
    else
    {
        jQuery(this).hide();
    }
}
