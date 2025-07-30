<?php 
$pageTitle = "Users";
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
    border-top: 3px solid #2bb240;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.sidenav {
  z-index: 1038;
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
                    <h6 class="text-white text-capitalize ps-3">Users</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    <div class="card-header pb-3 px-3">
                    <p class="mb-0">Manage app users and view key user data.
                    <a style="font-size: 12px;float: right; text-decoration: underline;" href="/event-organisers?add-new=true">New Event Organiser?</a>
                    </p>
                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="users" class="w-100 px-6">
                        <div id="grid" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden;"></div>
                        <div id="form" style="width: 100%; max-width:1000px; margin-top: 30px"></div>
                    </div>

                </div>
              </div>  
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>
    </div>
</main>
</body>
<script type="module">

const API_URL = "<?php echo $_ENV['API_URL']; ?>";
const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";  

// Loader Container
const loaderContainer = document.getElementById('loaderContainer');
loaderContainer.style.display = 'flex';
const usersContainer = document.getElementById('users');

let grid = new w2grid({
    name: 'grid',
    box: '#grid',
    header: 'App Users & Event Organisers',
    reorderRows: false,
    show: {
        header: true,
        footer: true,
        toolbar: true,
        lineNumbers: true
    },
    columns: [
        { field: 'recid', text: 'ID', size: '30px', sortable: true},
        { field: 'distance', text: 'Distance', size: '30px', sortable: true},
        { field: 'gender', text: 'Gender', size: '30px', sortable: true},
        { field: 'handicap', text: 'Handicap', size: '30px', sortable: true},
        { field: 'tees', text: 'Tees', size: '30px', sortable: true},
        { field: 'user_role', text: 'User Role', size: '200px', sortable: true },
        { field: 'name', text: 'First Name', size: '200px', sortable: true,
            info: {
                // This needs to be the user_preferences
                render: (rec, ind, col_ind) => { return '<i>' + rec.name + '</i> <b>' + rec.surname + '</b>' }
            }
        },
        { field: 'surname', text: 'Last Name', size: '200px', sortable: true },
        { field: 'email', text: 'Email', size: '300px', clipboardCopy: true, sortable: true },
        { field: 'contact_number', text: 'Contact Number', size: '200px', clipboardCopy: true, sortable: true },
        { field: 'status', text: 'Status', size: '200px', sortable: true },
        { field: 'sdate', text: 'Start Date', size: '200px', sortable: true }
    ],
    searches: [
        { type: 'int',  field: 'recid', label: 'ID' },
        { field: 'user_role', label: 'User Role', type: 'list',
            options: { items: ['Event Organiser', 'App User'] } },
        { type: 'text', field: 'name', label: 'First Name' },
        { type: 'text', field: 'surname', label: 'Last Name' },
        { type: 'date', field: 'sdate', label: 'Start Date' }
    ],
    onExpand(event) {
        query('#'+event.detail.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>')
    }
});
// create an ajax request using jQuery
$.ajax({
    url: API_URL+'api/v1/users/admin_users.php',
    dataType: 'json',
    type: 'POST',
    data: JSON.stringify({
        token: TOKEN
    }),
    success: function (data) {
        console.log(data);
        if (data && data.user_data) {
            // Clear existing records
            grid.clear();
            loaderContainer.style.display = 'none';
            usersContainer.style.display = 'block';

            // Process and add new records
            data.user_data.forEach(function(user) {
                // Check if user_role is 'event_organiser' and set the background color
                if (user.user_role === 'event_organiser') {
                    user.w2ui = { style: 'background-color: #98d5b2' };
                    user_role = 'Event Organiser';
                } else {
                    user_role = 'App User';
                }

                var distance;
                var gender;
                var handicap;
                var tees;

                if (user.gimme_user_preferences && Array.isArray(user.gimme_user_preferences) && user.gimme_user_preferences.length > 0) {
                    console.log(user.gimme_user_preferences[0].distance);
                    console.log(user.gimme_user_preferences[0].gender);
                    distance = user.gimme_user_preferences[0].distance;
                    gender = user.gimme_user_preferences[0].gender;
                    handicap = user.gimme_user_preferences[0].handicap;
                    tees = user.gimme_user_preferences[0].tees;

                } else {
                    console.log('No user preferences available');
                    user.gimme_user_preferences = [];
                    distance = '';
                    gender = '';
                    handicap = '';
                    tees = '';
                }

                grid.add({
                    recid: user.id,
                    user_role: user_role,
                    name: user.name,
                    surname: user.surname,
                    email: user.email,
                    contact_number: user.contact_number,
                    status: user.status,
                    distance: distance,
                    gender: gender,
                    handicap: handicap,
                    tees: tees,
                    sdate: user.created_at,
                    w2ui: user.w2ui || {},
                });
            });

            grid.refresh();
        }
    }
});


let status = [
    { id: 1, text: 'Active' },
    { id: 2, text: 'Blocked' }
]
let user_role = [
    { id: 1, text: 'User' },
    { id: 2, text: 'Event Organiser' }
]
let distance = [
    { id: 1, text: 'Metres' },
    { id: 2, text: 'Yards' },
    { id: 2, text: 'Feet' }
]
let gender = [
    { id: 1, text: 'Male' },
    { id: 2, text: 'Female' }
]
let form = new w2form({
    box: '#form',
    name: 'form',
    focus  : -1,
    url: 'server/post',
    header: 'Edit User',
    fields: [
        { field: 'name', type: 'text', required: true,
            html: { page: 0, label: 'Name', attr: 'style="width: 300px"' }
        },
        { field: 'surname', type: 'text', required: true,
            html: { page: 0, label: 'Surname', attr: 'style="width: 300px"' }
        },
        { field: 'email', type: 'email', required: true,
            html: {
                page: 0,
                label: 'Email',
                attr: 'style="width: 300px" readonly'
            }
        },
        { field: 'contact', type: 'alphaNumeric',
            html: {
                page: 0,
                label: 'Contact Number',
                attr: 'style="width: 300px" readonly',
                options: { maxLength: 10 }
            }
        },
        { field: 'date', type: 'text',
            html: { page: 0, label: 'Registration Timestamp', attr: 'style="width: 300px" readonly' }
        },
        { field: 'status', type: 'list', required: true,
            html: { page: 0, label: 'Status' },
            options: { items: w2utils.clone(status) }
        },
        { field: 'user_role', type: 'list', required: true,
            html: { page: 0, label: 'User Role' },
            options: { items: w2utils.clone(user_role) }
        },
        { field: 'distance', type: 'list',
            html: { page: 1, label: 'Distance', attr: 'readonly' },
            options: { items: w2utils.clone(distance) }
        },
        { field: 'gender', type: 'list',
            html: { page: 1, label: 'Gender', attr: 'readonly' },
            options: { items: w2utils.clone(gender) }
        },
        { field: 'handicap', type: 'text',
            html: { page: 1, label: 'Handicap', attr: 'style="width: 300px" readonly' }
        },
        { field: 'tees', type: 'text',
            html: { page: 1, label: 'Default Tees', attr: 'style="width: 300px" readonly' }
        },
        { field: 'prefdate', type: 'text',
            html: { page: 1, label: 'Registration Timestamp', attr: 'style="width: 300px" readonly' }
        },
    ],
    tabs: [
        { id: 'tab1', text: 'User Details' },
        { id: 'tab2', text: 'User Preferences' }
    ],
    actions: {
        Reset() {
            this.clear();
        },
        Save() {
            if (form.validate().length == 0) {
                w2popup.open({
                    title: 'Form Data',
                    with: 600,
                    height: 550,
                    body: `<pre>${JSON.stringify(this.getCleanRecord(), null, 4)}</pre>`,
                    actions: { Ok: w2popup.close }
                })
            }
        },
    }
});

grid.on('click', function(event) {
    console.log("Clicked event:", event);
    var record = this.get(event.detail.recid);
    console.log("Record found:", record);
    if (record) {
        var userRoleText = record.user_role;
        var userRoleId;

        if (userRoleText === 'User') {
            userRoleText = 'User';
            userRoleId = 1;
        } else if (userRoleText === 'Event Organiser') {
            userRoleText = 'Event Organiser';
            userRoleId = 2;
        }

        var activeText = record.status;
        var activeId;

        if (activeText === 'active') {
            activeText = 'Active';
            activeId = 1;
        } else if (activeText === 'blocked') {
            activeText = 'Blocked';
            activeId = 2;
        }

        var distanceText = record.distance;
        var distanceId;

        if (distanceText === 'metres') {
            distanceText = 'Metres';
            distanceId = 1;
        } else if (distanceText === 'yards') {
            distanceText = 'Yards';
            distanceId = 2;
        } else if (distanceText === 'feet') {
            distanceText = 'Feet';
            distanceId = 3;
        }

        var genderText = record.gender;
        var genderId;

        if (genderText === 'male'){
            genderText = 'Male';
            genderId = 1;
        } else {
            genderText = 'Female';
            genderId = 2;
        }


        // Set the form record
        form.record = {
            name: record.name,
            surname: record.surname,
            email: record.email,
            contact: record.contact_number,
            date: record.sdate,
            status: { id: activeId, text: activeText },
            user_role: { id: userRoleId, text: userRoleText },
            distance: { id: distanceId, text: distanceText },
            gender: {id: genderId, text: genderText },
            handicap: record.handicap,
            tees: record.tees,
            prefdate: record.sdate
        };

        form.refresh();
        console.log(form.record);
    }
});

grid.hideColumn('recid');
grid.hideColumn('distance');
grid.hideColumn('gender');
grid.hideColumn('handicap');
grid.hideColumn('tees');
</script>
</html>
