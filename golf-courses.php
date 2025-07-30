<?php 
$pageTitle = "Golf Courses";
$type = "user";
require_once 'session/session.php';
// require_once 'session/permission_handler.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>
<link href='https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.css' rel='stylesheet' />
<script src='https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.js'></script>


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

label {
    font-size: 12px;
}

#courses-select > div > div > div.w2ui-field.w2ui-span4 > div {
    margin-left: 0px !important;
}

#courses-select > div > div > div.w2ui-field.w2ui-span4 > div > input {
    width: 100% !important;
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
                        <h6 class="text-white text-capitalize ps-3">Golf Courses</h6>
                    </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    <div class="card-header pb-3 px-3">
                    <p class="mb-0">Manage Golf Course Information.</p>
                    </div>
                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>
                    <input type="hidden" id="edit_key" value="unset">

                    <?php include 'views/golf_courses_select.php'; ?>            

                    <div id="courses" class="w-100 px-6">
                        <div style="width: 100%; max-width: 1000px; margin-top: 30px; min-height: 100px;" class="w2ui-reset w2ui-form">
                            <div class="card card-body mx-3 mx-md-4 mt-2 mb-3">
                            <div class="h-100">
                            <h5 class="mb-1">
                                Course Details
                            </h5>
                            </div>
                                <div class="card-header pb-0 p-3">

                                <?php include 'views/golf_courses_general.php'; ?>

                                <?php include 'views/golf_courses_tees.php'; ?>

                                <?php include 'views/golf_courses_data.php'; ?>

                                    <div class="row p-4">
                                        <div class="w2ui-buttons">
                                            <button onclick="newCourseRedirect();" name="Reset" class="w2ui-btn " style="" tabindex="10">Add New Course</button>
                                            <button onclick="saveCourse();" name="Save" class="w2ui-btn w2ui-btn-blue" style="" tabindex="11">Save</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
              </div>  
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>
    </div>
</main>

<script>

mapboxgl.accessToken = "<?php echo $_ENV['MAPBOX_API']; ?>";
var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [24.676997, -28.48322], // longitude, latitude
    zoom: 3
    // show controls

});
var marker = new mapboxgl.Marker()
.setLngLat([24.676997, -28.48322])
.addTo(map);
map.addControl(new mapboxgl.NavigationControl(), 'top-right');

const API_URL = "<?php echo $_ENV['API_URL']; ?>";
const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";
var selectedCourse = null;
const loaderContainer = document.getElementById('loaderContainer');
loaderContainer.style.display = 'flex';

let courseData;

// Load Golf Course Selection // 
// create an ajax post request 
$.ajax({
    url: API_URL+'api/v1/courses/admin_golf_courses.php',
    dataType: 'json',
    type: 'POST',
    data: JSON.stringify({
        token: TOKEN
    }),
    success: function (data) {

        console.log(data);
        if (data && data.course_data) {
            loaderContainer.style.display = 'none';
            courseData = data.course_data;

            var course_names = data.course_names;
            let courses = data.course_names;
            courses.sort();

            new w2field('list', {
                el: query('input[type=list]')[0],
                items: courses,
                match: 'contains',
                markSearch: true,
                onSelect(event) {
                    console.log('Selected:', event.detail.item)
                }
            })
        }
    }
});

function saveCourse() {

  // Capture course name
  var courseName = document.getElementById("course_name").value;
  
  // Validate course name
  if (!courseName) {
    gimmeToast("Course name is required", "error");
    return;
  }
  
  // Capture course GPS location
  var courseGPS = document.getElementById("course_gps").value;

  // Validate course GPS  
  if (!courseGPS) {
    gimmeToast("Course GPS is required", "error");
    return;
  }

  // Capture course description
  var courseDescription = document.getElementById("course_description").value;

  // Validate course description
  if (!courseDescription) {
    gimmeToast("Course description is required", "error");
    return;
  }

  // Capture tee data
  var teeData = {};
  var teeElements = document.querySelectorAll("#showTees li");
  teeElements.forEach(function(tee) {
    var teeName = tee.querySelector("h6 input").value;
    var teePar = tee.querySelectorAll("input")[0].value;
    var teeRating = tee.querySelectorAll("input")[1].value;
    var teeSlope = tee.querySelectorAll("input")[2].value;
    
    // Validate tee data
    if (!teeName || !teePar || !teeRating || !teeSlope) {
      gimmeToast("All tee data is required", "error");
      return;
    }

    teeData[teeName] = {
      par: teePar,
      rating: teeRating,
      slope: teeSlope
    };
  });

  // Capture default men's tees
  var mensTees = document.getElementById("mens_tees").value;

  // Validate men's tees
  if (!mensTees) {
    gimmeToast("Default men's tees are required", "error");
    return;
  }  

  // Capture default women's tees
  var womensTees = document.getElementById("womens_tees").value;

  // Validate women's tees
  if (!womensTees) {
    gimmeToast("Default women's tees are required", "error");
    return; 
  }

  // Capture hole data
  var holeData = {};
  for (var i = 1; i <= 18; i++) {
    var pinGPS = document.getElementById("pinGPS-" + i).value;
    var teeGPS = document.getElementById("teeGPS-" + i).value;
    
    // Validate hole data
    if (!pinGPS || !teeGPS) {
      gimmeToast("All hole data is required", "error");
      return;
    }
    holeData["hole_" + i] = {
      pin_location_gps: pinGPS,
      tee_box: teeGPS,
      // Not required
      par: document.getElementById("par-" + i).value,
    //   yards: document.getElementById("holeYards-" + i).value,
      hole_name: document.getElementById("holeName-" + i).value,
      from_the_tee: document.getElementById("fromTheTee-" + i).value,
      on_the_green: document.getElementById("onTheGreen-" + i).value,
      from_the_fairway: document.getElementById("fromTheFairway-" + i).value,
    };
  }

  // Capture stimp reading
  var stimpReading = document.getElementById("stimp_read").value;

  // Validate stimp reading
  if (!stimpReading) {
    gimmeToast("Stimp reading is required", "error");
    return;
  }

  // Log data to console
  console.log("Course Name: " + courseName);
  console.log("Course GPS: " + courseGPS);
  console.log("Course Description: " + courseDescription);
  console.log("Tee Data: ", teeData);
  console.log("Men's Tees: " + mensTees);
  console.log("Women's Tees: " + womensTees);
  console.log("Hole Data: ", holeData);
  console.log("Stimp Reading: " + stimpReading);

    if(selectedCourse != null){
        $.ajax({
            url: API_URL+"api/v1/courses/update_course.php",
            method: "POST",
            headers: {
                "Access-Control-Allow-Origin": "*",
                "Content-Type": "application/json"
            },
            data: JSON.stringify({
                token:TOKEN,
                course_id: selectedCourse,
                course_name: courseName,
                course_gps: courseGPS,
                course_description: courseDescription,
                teeData: teeData,
                mensTees: mensTees,
                womensTees: womensTees,
                holeData: holeData,
                stimpReading: stimpReading
            }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success:function(response){
                console.log("Result");
                console.log(response);
                gimmeToast("Course Details Updated", "success");
            },
            error:function(error){
                console.log("Error");
                console.log(error);
            }
        });
    }else{
        console.log("API request not made")
    }
}

var loadedCourseData = false;
function loadCourse(){
    loadedCourseData = true;
    var courseName = document.getElementById("golf_course_search").value;
    var selected = document.getElementById("golf_course_search").getAttribute("data-selected");

    if (Array.isArray(courseData)) {
        gimmeToast("Edit course details below", "success");
        var matchingCourse = courseData.find(course => course.course_name === selected);
        
        // Debug
        console.log("Course");
        console.log(matchingCourse);
        //--------------------------------------

        // Store selcted course ID
        selectedCourse = matchingCourse.id;

        if (matchingCourse) {
            document.getElementById("course_name").value = selected;
            document.getElementById("course_gps").value = matchingCourse.location_gps;

            // Set the map
            var gpsArray = matchingCourse.location_gps.split(',').map(Number);

            var map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v11',
                center: gpsArray, // [longitude, latitude]
                zoom: 12
            });

            var marker = new mapboxgl.Marker()
                .setLngLat(gpsArray)
                .addTo(map);

            map.addControl(new mapboxgl.NavigationControl(), 'top-right');


            document.getElementById("course_description").value = matchingCourse.course_description;

            var tee_data = matchingCourse.tee_data;
            console.log(tee_data);

            var tee_html = '';
            var tees_list = [];
            document.getElementById("showTees").innerHTML = tee_html;

            if(tee_data != null){
                for (var key in tee_data.tees) {
                    if (tee_data.tees.hasOwnProperty(key)) {
                        var tee = tee_data.tees[key];
                        // tee to tees_list
                        var tees_name = key;
                        // capitalize the first letter of each word
                        tees_name = tees_name.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
                        tees_list.push(tees_name);
                        console.log(tee);
                        var uuid = Math.floor(Math.random() * 1000000000);

                        tee_html += '<li id="'+uuid+'" class="tee-data list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg"><div class="d-flex flex-column"><h6 class="mb-3 text-sm"><input type="text" id="w2int" placeholder="e.g. Yellow Tees" value="'+key+'"></h6><span class="mb-2 text-xs">Par: <span class="text-dark font-weight-bold ms-sm-2"><input id="w2int" value="'+tee.par+'"></span></span><span class="mb-2 text-xs">Rating: <span class="text-dark ms-sm-2 font-weight-bold"><input id="w2int" value="'+tee.rating+'"></span></span><span class="text-xs">Slope: <span class="text-dark ms-sm-2 font-weight-bold"><input id="w2int" value="'+tee.slope+'"></span></span></div><div class="ms-auto text-end"><a onclick="removeTees('+uuid+')" class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;"><i class="material-icons text-sm me-2">delete</i>Delete</a></div></li>';
                    }
                }
            }

            document.getElementById("showTees").innerHTML = tee_html;

            // set the mens and womens default tees
            var mens_tees = matchingCourse.default_tees_men;
            mens_tees = mens_tees.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
            var womens_tees = matchingCourse.default_tees_woman;
            womens_tees = womens_tees.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});

            let tees = tees_list;
            tees.sort();

            new w2field('list', {
                el: query('input[type=list]')[1],
                items: tees,
                match: 'contains',
                markSearch: true,
                onSelect(event) {
                    console.log('Selected:', event.detail.item)
                }
            });

            document.getElementById("mens_tees").value = mens_tees;

            let wtees = tees_list;
            wtees.sort();

            new w2field('list', {
                el: query('input[type=list]')[2],
                items: wtees,
                match: 'contains',
                markSearch: true,
                onSelect(event) {
                    console.log('Selected:', event.detail.item)
                }
            });

            document.getElementById("womens_tees").value = womens_tees;
            
            // Assuming hole_data has been correctly retrieved from matchingCourse.course_data
            var hole_data = matchingCourse.course_data.course_data.hole_data;
            var stimp_reading = matchingCourse.course_data.course_data.stimp_reading;

            // Loop through each hole and set the values for pin and tee GPS locations
            for (var hole in hole_data) {
                if (hole_data.hasOwnProperty(hole)) {

                    // Debug
                    console.log("Hole is:")
                    console.log(hole_data)
                    // ------------------------------

                    var holeNumber = hole.split('_')[1];

                    // Set pin GPS location
                    var pinGPSInput = document.getElementById('pinGPS-' + holeNumber);
                    if (pinGPSInput) {
                        pinGPSInput.value = hole_data[hole].pin_location_gps === "default" ? "" : hole_data[hole].pin_location_gps;
                    }

                    // Set tee GPS location, leave empty if "default"
                    var teeGPSInput = document.getElementById('teeGPS-' + holeNumber);
                    if (teeGPSInput) {
                        teeGPSInput.value = hole_data[hole].tee_box === "default" ? "" : hole_data[hole].tee_box;
                    }

                    // Set Par
                    var parInput = document.querySelector('#par-'+holeNumber);
                    if(parInput){
                        parInput.value = hole_data[hole].par;
                    }

                    // Set Hole Name
                    var holeNameInput = document.querySelector('#holeName-'+holeNumber);
                    if(holeNameInput){
                        holeNameInput.value = hole_data[hole].hole_name;
                    }

                    // Set Tee
                    var fromTheTee = document.querySelector('#fromTheTee-'+holeNumber);
                    if(fromTheTee){
                        fromTheTee.value = hole_data[hole].from_the_tee;
                    }

                    // Set Green
                    var onTheGreen = document.querySelector('#onTheGreen-'+holeNumber);
                    if(onTheGreen){
                        onTheGreen.value = hole_data[hole].on_the_green;
                    }

                    // Set Green
                    var fromTheFairway = document.querySelector('#fromTheFairway-'+holeNumber);
                    if(fromTheFairway){
                        fromTheFairway.value = hole_data[hole].from_the_fairway;
                    }
                }
            }

            // Set the stimp reading value
            var stimpInput = document.getElementById('stimp_read');
            if (stimpInput) {
                stimpInput.value = stimp_reading;
            }

        } else {
            gimmeToast("Course not found", "error");
        }
    } else {
        gimmeToast("Course not available", "error");
    }
}

let tees = ['No Tees'];
tees.sort();

new w2field('list', {
    el: query('input[type=list]')[1],
    items: tees,
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
});

let wtees = ['No Tees'];
wtees.sort();

new w2field('list', {
    el: query('input[type=list]')[2],
    items: wtees,
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
});

new w2field({ el: query('#w2int')[0], type: 'int', autoFormat: false })
new w2field({ el: query('#w2int')[1], type: 'float', autoFormat: false })
new w2field({ el: query('#pinGPS')[0], type: 'float', autoFormat: false })
new w2field({ el: query('#teeGPS')[0], type: 'float', autoFormat: false })
new w2field({ el: query('#w2int')[2], type: 'int', autoFormat: false })
new w2field({ el: query('#w2int')[3], type: 'int', autoFormat: false })

// Add New Tees
function newTees(){
    var uuid = Math.floor(Math.random() * 1000000000);

    var tee_html = '<li id="'+uuid+'" class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg"><div class="d-flex flex-column"><h6 class="mb-3 text-sm"><input type="text" id="w2int" placeholder="e.g. Yellow Tees"></h6><span class="mb-2 text-xs">Par: <span class="text-dark font-weight-bold ms-sm-2"><input id="w2int" value="72"></span></span><span class="mb-2 text-xs">Rating: <span class="text-dark ms-sm-2 font-weight-bold"><input id="w2int" value="72.0"></span></span><span class="text-xs">Slope: <span class="text-dark ms-sm-2 font-weight-bold"><input id="w2int" value="136"></span></span></div><div class="ms-auto text-end"><a onclick="removeTees('+uuid+')" class="btn btn-link text-danger text-gradient px-3 mb-0" href="javascript:;"><i class="material-icons text-sm me-2">delete</i>Delete</a></div></li>';
    document.getElementById("showTees").innerHTML += tee_html;

}

function removeTees(uuid){
    var uuid = uuid;
    var element = document.getElementById(uuid);
    // create a dialog warning the user that they will lose all unsaved data
    var r = confirm("Are you sure you delete this tees group.");
    if (r == true) {
        element.parentNode.removeChild(element);
    }
    
}

// New Course Redirect
function newCourseRedirect(){
    
    if(loadedCourseData != true){
        // Capture course name
        var courseName = document.getElementById("course_name").value;
        
        // Validate course name
        if (!courseName) {
            gimmeToast("Course name is required", "error");
            return;
        }
        
        // Capture course GPS location
        var courseGPS = document.getElementById("course_gps").value;

        // Validate course GPS  
        if (!courseGPS) {
            gimmeToast("Course GPS is required", "error");
            return;
        }

        // Capture course description
        var courseDescription = document.getElementById("course_description").value;

        // Validate course description
        if (!courseDescription) {
            gimmeToast("Course description is required", "error");
            return;
        }

        // Capture tee data
        var teeData = {};
        var teeElements = document.querySelectorAll("#showTees li");
        teeElements.forEach(function(tee) {
            var teeName = tee.querySelector("h6 input").value;
            var teePar = tee.querySelectorAll("input")[0].value;
            var teeRating = tee.querySelectorAll("input")[1].value;
            var teeSlope = tee.querySelectorAll("input")[2].value;
            
            // Validate tee data
            if (!teeName || !teePar || !teeRating || !teeSlope) {
                gimmeToast("All tee data is required", "error");
                return;
            }

            teeData[teeName] = {
            par: teePar,
            rating: teeRating,
            slope: teeSlope
            };
        });

        // Capture default men's tees
        var mensTees = document.getElementById("mens_tees").value;

        // Validate men's tees
        if (!mensTees) {
            gimmeToast("Default men's tees are required", "error");
            return;
        }  

        // Capture default women's tees
        var womensTees = document.getElementById("womens_tees").value;

        // Validate women's tees
        if (!womensTees) {
            gimmeToast("Default women's tees are required", "error");
            return; 
        }

        // Capture hole data
        var holeData = {};
        for (var i = 1; i <= 18; i++) {
            var pinGPS = document.getElementById("pinGPS-" + i).value;
            var teeGPS = document.getElementById("teeGPS-" + i).value;
            
            // Validate hole data
            if (!pinGPS || !teeGPS) {
            gimmeToast("All hole data is required", "error");
            return;
            }
            holeData["hole_" + i] = {
            pin_location_gps: pinGPS,
            tee_box: teeGPS,
            // Not required
            par: document.getElementById("par-" + i).value,
            //   yards: document.getElementById("holeYards-" + i).value,
            hole_name: document.getElementById("holeName-" + i).value,
            from_the_tee: document.getElementById("fromTheTee-" + i).value,
            on_the_green: document.getElementById("onTheGreen-" + i).value,
            from_the_fairway: document.getElementById("fromTheFairway-" + i).value,
            };
        }

        // Capture stimp reading
        var stimpReading = document.getElementById("stimp_read").value;

        // Validate stimp reading
        if (!stimpReading) {
            gimmeToast("Stimp reading is required", "error");
            return;
        }

        // Log data to console
        console.log("Course Name: " + courseName);
        console.log("Course GPS: " + courseGPS);
        console.log("Course Description: " + courseDescription);
        console.log("Tee Data: ", teeData);
        console.log("Men's Tees: " + mensTees);
        console.log("Women's Tees: " + womensTees);
        console.log("Hole Data: ", holeData);
        console.log("Stimp Reading: " + stimpReading);

        $.ajax({
            url: API_URL+"api/v1/courses/save_course.php",
            method: "POST",
            headers: {
                "Access-Control-Allow-Origin": "*",
                "Content-Type": "application/json"
            },
            data: JSON.stringify({
                token:TOKEN,
                course_name: courseName,
                course_gps: courseGPS,
                course_description: courseDescription,
                teeData: teeData,
                mensTees: mensTees,
                womensTees: womensTees,
                holeData: holeData,
                stimpReading: stimpReading
            }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success:function(response){
                console.log("Result");
                console.log(response);
                gimmeToast("Course Details Updated", "success");
            },
            error:function(error){
                console.log("Error");
                console.log(error);
            }
        });
    }else{
        gimmeToast("Course already exists.", "error");
    }
}
</script>    
</body>
</html>
