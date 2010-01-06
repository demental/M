<link rel="stylesheet" type="text/css" media="screen" href="/css/otfimage.css" />
<script type="text/javascript" src="/js/jquery.block.js"></script>
<script type="text/javascript" src="/js/AjaxUpload.2.0.js"></script>
<script type="text/javascript">
var initpheditor;
$(function(){
  initpheditor = function(blockid) {
  $('#showimagelist_'+blockid).toggle(function(){$('#imagelist_'+blockid).hide()},function(){$('#imagelist_'+blockid).show()});
  $('.otf_deletephoto').click(function(){
    var link=this;
    if(confirm('Voulez-vous supprimer cette photo ?')) {
      $.get($(this).attr('href'),function(){
        $(link).parent().remove();
      });
    }
    return false;
  });
  $('.otf_setphotoasmain').click(function(){
    var link=this;
    $.get($(this).attr('href'),function(){
      $(link).parent().parent().find('.main').removeClass('main');
      $(link).parent().addClass('main');
    });    
    return false;
  })
  new Ajax_upload($('#addphotolink_'+blockid),
                  { action:$('#addphotolink_'+blockid).attr('href'),
                    name:'filename',
                    onSubmit:function(){
                      $.blockUI({message:$('#waitmessage')});
                    },
                    onComplete:function(){
                      $.unblockUI();
                      $('#photowidget_'+blockid)
                      .load($('#showimagelist_'+blockid).attr('href'),function(){initpheditor(blockid)});                                    
                    }
                  });
  }
})
</script>
<div id="waitmessage" style="display:none">
  <?php _e('Image loading')?>....
</div>