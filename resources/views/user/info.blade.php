<html>
  <head>
     <link rel="stylesheet" href="https://cdn.bootcss.com/twitter-bootstrap/3.3.2/css/bootstrap.min.css">
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
    <table class="table table-striped">
      <tbody id="result">
      </tbody>
    </table>
    <script src="https://cdn.bootcss.com/jquery/2.1.2/jquery.min.js"></script>
    <script>
      $(function(){
        $('button').click(function(){
          $.ajax({
            type: 'POST',
            data: $('form').serialize(),
            success: function(data) {
              var html;
              $.each(data, function(i, n){
                html += '<tr> <td>'+n.id+'</td> <td>'+n.email+'</td> <td>'+n.software_version+'</td> <td>'+n.firmware_version+'</td> <td>'+n.system_version+'</td>  <td>'+n.ip+'</td>  <td>'+n.created_at+'</td> </tr>';
              })
              $('#result').html(html);
            }
          })
          return false;
        })
      })
    </script>
  </body>
</html>