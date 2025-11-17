<!DOCTYPE html>
<html>
<head>
  <title>Reorder</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/akottr/dragtable@master/dragtable.css" />
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-sm-12">
      <table id="tblReg" class="table table-bordered">
        <thead>
          <tr class="active">
            <th id="number">#</th>
            <th id="fname">First Name</th>
            <th id="lname">Last Name</th>
            <th id="uname">Username</th>
            <th id="pass">Password</th>
            <th id="email">Email</th>
            <th id="phone">Phone</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
            <td>545trt574</td>
            <td>mark@example.com</td>
            <td>7788994320</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
            <td>yffft5456</td>
            <td>jacob@example.com</td>
            <td>7788994320</td>
          </tr>
          <tr>
            <td>3</td>
            <td>Larry</td>
            <td>the Bird</td>
            <td>@twitter</td>
            <td>fgfhgf444</td>
            <td>larry@example.com</td>
            <td>7788994320</td>
          </tr>
          <tr>
            <td>4</td>
            <td>Rima</td>
            <td>the Bird</td>
            <td>@twitter</td>
            <td>jjk8899</td>
            <td>rima@example.com</td>
            <td>7788994320</td>
          </tr>
          <tr>
            <td>5</td>
            <td>Sundar</td>
            <td>the Bird</td>
            <td>@twitter</td>
            <td>76767687hjh</td>
            <td>sundar@example.com</td>
            <td>7788994320</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12">
      <a href="#" class="btn btn-info order">Get Table Order</a>
      <p class="porder"></p>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/akottr/dragtable@master/jquery.dragtable.js"></script>

<script type="text/javascript">
$(document).ready(function() {
  $('#tblReg').dragtable({
    persistState: function(table) {
      if (!window.sessionStorage) return;
      var ss = window.sessionStorage;
      table.el.find('th').each(function(i) {
        if (this.id !== '') {
          table.sortOrder[this.id] = i;
        }
      });
      ss.setItem('tableorder', JSON.stringify(table.sortOrder));
    },
    restoreState: JSON.parse(window.sessionStorage.getItem('tableorder') || '{}')
  });

  $('a.order').click(function(e) {
    e.preventDefault();
    var order_array = [];
    $('#tblReg thead th').each(function() {
      order_array.push($(this).text());
    });
    console.log(order_array);
    $('.porder').text(order_array.join(', '));
  });
});
</script>
</body>
</html>
