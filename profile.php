<?php 
$pageTitle = "Profile";
require_once 'session/session.php';
// require_once 'session/permission_handler.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>

<body class="g-sidenav-show bg-gray-200">

<?php include 'partials/left-nav.php'; ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<style>
.border-radius-xl {
    border-radius: 0.15rem;
}
.icon-lg {
    width: 45px;
    height: 45px;
}    
.loader {
    display: inline-block;
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 3px solid #3498db;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.sidenav {
  z-index: 1038;
}
input[type=button].w2ui-btn-blue, button.w2ui-btn-blue {
    color: white;
    background-image: linear-gradient(#2bb240 0%, #2bb240 100%);
    border: 1px solid #1d832c;
    text-shadow: none;
    text-transform: capitalize;
    font-size: 15px;
    font-weight: 600;
}
</style>    
    <!-- Navbar -->
        <?php include 'partials/top-nav.php'; ?>
    <!-- End Navbar -->


    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
              <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3">Profile</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    <div class="card-header pb-3 px-3">
                        <p class="mb-0">Manage account settings</p>
                    </div>

                    <div class="w-100 p-6">
                        <div id="form" style="width: 600px;"></div>
                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                    </div>
              </div>  
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>
    </div>
</main>
<script>
let form = new w2form({
    box: '#form',
    name: 'form',
    header: 'Edit Profile',
    fields: [
        { field: 'name', type: 'text', required: true,
            html: {
                label: 'Name',
                attr: 'style="width: 250px"',
            }
        },
        { field: 'surname', type: 'text', required: true,
            html: {
                label: 'Surname',
                attr: 'style="width: 250px"'
            }
        },
        { field: 'email', type: 'email', required: true,
            html: {
                label: 'Email',
                attr: 'style="width: 250px" readonly'
            }
        },
        { field: 'contact', type: 'alphaNumeric', required: true,
            html: {
                label: 'Contact Number',
                attr: 'style="width: 250px"',
                options: { maxLength: 10 }
            }
        }
    ],
    record: {
        name: '<?php echo $_SESSION['USER_NAME']; ?>',
        surname: '<?php echo $_SESSION['USER_SURNAME']; ?>',
        email: '<?php echo $_SESSION['USER_EMAIL']; ?>',
        contact: '<?php echo $_SESSION['USER_CONTACT']; ?>'
    },
    onValidate(event) {
        // and is number
        if (String(this.record.contact).length != 10) {
            // add custom error
            event.detail.errors.push({
                error: 'Phone must be 10 digits',
                field: this.get('contact')
            })
        }
    },
    actions: {
        save: function () {
            if (this.validate().length === 0) {
                gimmeToast("Profile updated", "success");
            } else {
                gimmeToast("Invalid, please check the profile", "error");
            }
        }
    }
});
</script>
</body>
</html>
