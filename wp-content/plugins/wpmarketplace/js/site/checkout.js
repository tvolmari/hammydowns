jQuery(function($){
    
    $('.cart_form').submit(function(e){
        e.preventDefault();
        var form = $(this);
        form.find("button[type=submit]").html("<i class='fa fa-spin fa-spinner'></i> Adding...");  
        form.ajaxSubmit({
            success: function(res){
              form.find("button[type=submit]").html("<i class='fa fa-check-circle'></i> Added").after("<div class='alert alert-success' style='position:absolute;z-index:99999;padding:3px 10px;'><a href='"+res+"'>View Cart</a></div>");  
            }
        });
        
        return false;
        
    });
    
     
    
});