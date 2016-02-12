/*wp-rwd-capture plugin*/
jQuery(function($){
  $('img').on("error", function(){
    var failover = $(this).data('failover');
    if (this.src != failover){
    	this.src = failover;
	$(this).removeAttr("width");
    }
  });
});
