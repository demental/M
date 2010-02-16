<style type="text/css">
#blackboard {
  background:#000;
  height:400px;
  overflow:auto;

  color:#afa;
}
#blackboard h2 {
  background:#444;
  font-weight:bold;
  font-size:150%;
  color:#fff;
  font-variant:small-caps;
  margin:0;
  padding:0 1em;
}
#blackboardcontainer {
  border:1px solid #888;
  padding:1em;
}
#blackboardcontainer a {
  color:#ccf;
  border-bottom:1px dotted #fff;
}
#blackboardcontainer textarea {
  width:100%;
}
#blackboardcontainer strong {
  color:#0a0;
  font-weight:bold;
}
#blackboardcontainer em {
  font-style:italic;
  color:#efe;
}
.homeblock {
  width:80%;
  height:90px;
  background:#ccc;
  margin:0 auto 1em auto;
  border:1px solid #888;
}
.homeblock h3 {
  background:#ddd;
  color:#000;
  border-bottom:2px solid #000;
  padding:0 1em;
  font-weight:bold;
}
.homeblockcontainer {
  padding:0.5em;
}
.timeline {
  background:#dfd;
}
.tasks {
    background:#fdd;
}
</style>
<div id="blackboard">
  <h2>Tableau noir</h2>
  <div id="blackboardcontainer">

    <?php echo $this->c('home','addmessage')?>
    <ul>
    <?php foreach($messages as $mess):?>
      <?php $this->i('home/messageline',array('mess'=>$mess))?>
    <?php endforeach?>
    </ul>
  </div>
</div>    
<script type="text/javascript" src="/js/jquery.form.js"></script>
<script type="text/javascript">
$(function(){
  $('#addmessageform').ajaxForm({clearForm:true,success:function(response){
    $('#blackboardcontainer ul').prepend(response);
  }})
}) 
</script>