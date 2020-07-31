<html>
    <head>

        <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    
    </head>
    <body id="LoginForm">
        <div class="container">
            <h1 class="form-heading">Welcome to Smart Home</h1>
            <div class="login-form">
                <div class="main-div">
                    <div class="panel">
                        <p>Please enter your username and password</p>
                    </div>
                    <form id="Login">
                        <div class="form-group">
                            <input type="name" class="form-control" id="inputUsername" placeholder="User Name">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="inputPassword" placeholder="Password">
                        </div>
                    </form>
                </div>
                <button type="submit" class="btn btn-primary" onClick='onLogin()'>Login</button>
            </div>
        </div>

        <script>
            function onLogin() {
                const user = document.getElementById("inputUsername").value;
                const pass = document.getElementById("inputPassword").value;
                console.log(user);
                console.log(pass);
                if (user == "admin" && pass == "123" ) {
                    setCookie("pass",pass,0);
                    // document.cookie = 'username=' + user + '; SameSite=None; Secure';
                    // document.cookie = "username=bar";
                    document.location.href = '/smartHome/landpage.php';
                } else {
                    if(!alert("Wrong Username / Password combination\nTry again!")){window.location.reload();}
                }
            }
        </script>

        <script> 
            function setCookie(cname, cvalue, exdays) {
                var d = new Date();
                // d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                var expires = "expires="+d.toUTCString();
                // document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
                document.cookie = cname + "=" + cvalue + ";path=/";
            }

            function getCookieValue(a) {
                var b = document.cookie.match('(^|[^;]+)\\s*' + a + '\\s*=\\s*([^;]+)');
                return b ? b.pop() : '';
            }
        </script>
        
    </body>
</html>
