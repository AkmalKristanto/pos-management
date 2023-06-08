<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Register Pantes Gold</title>

	<link href="img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
	<link href="img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
	<link href="img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
	<link href="img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
	<link href="img/favicon.png" rel="icon" type="image/png">
	<link href="img/favicon.ico" rel="shortcut icon">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
<link rel="stylesheet" href="{{url('template/starui/build')}}/css/separate/pages/login.min.css">
    <link rel="stylesheet" href="{{url('template/starui/build')}}/css/lib/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="{{url('template/starui/build')}}/css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="{{url('template/starui/build')}}/css/main.css">
<link rel="stylesheet" href="{{url('template/starui/build')}}/css/lib/bootstrap-sweetalert/sweetalert.css">
<link rel="stylesheet" href="{{url('template/starui/build')}}/css/separate/vendor/sweet-alert-animations.min.css">
<style type="text/css">
    .eror{
        color: red;
        background-color: #ffeced;
        margin: 11px;
    }
</style>
</head>
<body>

    <div class="page-center">
        <div class="page-center-in">
            <div class="container-fluid" style="    padding: 90px 0;">
                <form class="sign-box" id="example-form" action="{{url('/register-sales/store')}}" method="POST" autocomplete="off" enctype="multipart/form-data">
                    <!-- <div class="sign-avatar no-photo">&plus;</div> -->
                    @csrf
                    <header class="sign-title">Sign Up Sales</header>
                    <div class="form-group">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" placeholder="Nama User" name="Nama_User" required/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Telepon</label>
                        <input type="number" class="form-control @error('No_Telepon') is-invalid @enderror" id="No_Telepon" placeholder="No Telepon" name="No_Telepon" value="{{ old('email') }}" required/>
                        @error('No_Telepon')
                            <span class="invalid-feedback" role="alert">
                                <p class="eror">{{ $message }}</p>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" placeholder="Password Minimal 6 Character" name="password" required/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Confirmation</label>
                        <input type="password" class="form-control" placeholder="Repeat password" name="password_confirmation" required/>
                    </div>
                    <!-- <div class="form-group">
                        <select class="form-control show-tick" id="tipe_role" name="id_role" required>
                            <option selected="" disabled="">Pilih Role</option>
                            @foreach($role as $val)
                            <option value="{{$val->Id_Role}}">{{ $val->Nama_Role }}</option>
                            @endforeach
                        </select>
                    </div> -->
                    <div class="form-group" id="toko" style="" >
                        <label class="form-label">Cabang Toko</label>
                        <select class="form-control" name="kd_toko" required>
                            <!-- <option selected="" disabled="">Pilih Role</option> -->
                            @foreach($toko as $val)
                            <option value="{{$val->Kd_Toko}}">{{ $val->Nama_Toko }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-rounded btn-success sign-up">Sign up</button>
                    <!-- <p class="sign-note">Already have an account? <a href="sign-in.html">Sign in</a></p> -->
                    <!--<button type="button" class="close">
                        <span aria-hidden="true">&times;</span>
                    </button>-->
                </form>
            </div>
        </div>
    </div><!--.page-center-->


<script src="{{url('public/moment-2.25.1/js')}}/moment.min.js"></script>
    <script>
    $('#tipe_role').on('change',function(){
        var Id_Role = $(this).val();
        $.ajax({
            url:'{{url('/api/check-role/')}}/'+Id_Role,
            type: 'GET',
            success: function(data) {
                if(data.Id_Role == '1') {
                    $("#toko").show()
                }else {
                    $("#toko").hide()
                }
            }
        })
    });
    </script>
    <script>
        $(function() {
            $('.page-center').matchHeight({
                target: $('html')
            });

            $(window).resize(function(){
                setTimeout(function(){
                    $('.page-center').matchHeight({ remove: true });
                    $('.page-center').matchHeight({
                        target: $('html')
                    });
                },100);
            });
        });
        $("#example-form").submit(function(e) {
            var form = this;
            e.preventDefault();
            swal({
                title: "Apakah Anda Yakin?",
                text: "Anda akan mengubah data ini",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Simpan",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    form.submit();
                } else {
                    swal({
                        title: "Batal",
                        text: "Aksi dibatalkan",
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
                }
            });
        });
    </script>
</body>
</html>