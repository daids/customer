<html>
  <head>
     <link rel="stylesheet" href="https://cdn.bootcss.com/twitter-bootstrap/3.3.2/css/bootstrap.min.css">
     <script src="https://cdn.bootcss.com/jquery/2.1.2/jquery.min.js"></script>
     <script scr="//unpkg.com/json-highlight"></script>
  </head>
  <body>
    <form class="form-horizontal">
    <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label" for="email">用户邮箱</label>  
      <div class="col-md-4">
      <input id="email" name="email" type="text" placeholder="" class="form-control input-md" required="">
      <span class="help-block">输入用户邮箱地址</span>  
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label" for="password">查询密码</label>
      <div class="col-md-4">
        <input id="password" name="password" type="password" placeholder="" class="form-control input-md" required="">
        <span class="help-block">输入查询密码</span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label" for="tijiao"></label>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary">提交</button>
      </div>
    </div>
    </fieldset>
    </form>
    <div id="result">
      
    </div>
    <script>
      $(function(){
        $('button').click(function(){
          $.ajax({
            type: 'POST',
            data: $('form').serialize(),
            success: function(data) {
              $('#result').text(jsonHighlight(data));
            }
          })
          return false;
        })
      })
    </script>
  </body>
</html>