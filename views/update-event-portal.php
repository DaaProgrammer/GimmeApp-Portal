<div id="settings-portal">
    <!-- Loader Container -->
    <div id="portal-loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
        <div class="loader"></div>
    </div>

    <div class="form">

        <!-- UPLOAD CARE -->
        <script type="module">
            import * as LR from "https://cdn.jsdelivr.net/npm/@uploadcare/blocks@0.35.2/web/lr-file-uploader-regular.min.js";
            LR.registerBlocks(LR);
        </script>

        <div class="form-input">
            <label for="event-badge">Badge</label>
            <div id="event-badge">
                <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                <lr-config
                    ctx-name="badge-uploader"
                    pubkey="f80a9d210d3998ceb907"
                    max-local-file-size-bytes="500000000"
                    multiple="false"
                    source-list="local, url, gdrive"
                    crop-preset="1:1"
                    use-cloud-image-editor="true"
                    image-shrink="300x300 center"
                    img-only="true"
                    store="0"
                    ></lr-config>
                <lr-file-uploader-regular
                    css-src="https://cdn.jsdelivr.net/npm/@uploadcare/blocks@0.35.2/web/lr-file-uploader-regular.min.css"
                    ctx-name="badge-uploader"
                    class="my-config"
                >
                </lr-file-uploader-regular>

                <lr-upload-ctx-provider
                    ctx-name="badge-uploader"
                    id="badge-uploader"
                ></lr-upload-ctx-provider>
                <?php endif; ?>
            </div>
            <img src="<?= $currentEvent->event_badge ? $currentEvent->event_badge : "/assets/img/default/event_badge.png" ?>" alt="No Image Selected" style="margin-top:10px;border-radius:12px;width:120px;height:120px" id="badge-image">
        </div>

        <div class="form-input">
            <label for="event-banner">Banner</label>
            <div id="event-banner">
                <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
                <lr-config
                    ctx-name="banner-uploader"
                    pubkey="f80a9d210d3998ceb907"
                    max-local-file-size-bytes="500000000"
                    multiple="false"
                    source-list="local, url, gdrive"
                    crop-preset="16:9"
                    use-cloud-image-editor="true"
                    img-only="true"
                ></lr-config>
                <lr-file-uploader-regular
                css-src="https://cdn.jsdelivr.net/npm/@uploadcare/blocks@0.35.2/web/lr-file-uploader-regular.min.css"
                ctx-name="banner-uploader"
                class="my-config"
                >
                </lr-file-uploader-regular>

                <lr-upload-ctx-provider
                    ctx-name="banner-uploader"
                    id="banner-uploader"
                ></lr-upload-ctx-provider>
                <?php endif; ?>
            </div>
            <img src="<?= $currentEvent->event_banner ? $currentEvent->event_banner : "/assets/img/default/event_banner.jpg" ?>" alt="No Image Selected" style="margin-top:10px;border-radius:12px;width:1200px; height:400px;max-width: 100%;object-fit: fill;" id="banner-image">
        </div>
        <!-- UPLOAD CARE -->

        <!-- Color Picker -->
        <div class="form-input">
            <label>Color:</label>
            <input type="color" id="w2color" style="text-align: left" value="<?= $currentEvent->event_colour ?>" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?> >
        </div>

        <div class="form-input">
            <label for="event-description">Description</label>
            <textarea class="w2ui-input" name="event-description" id="event-description" <?= $_SESSION['USER_ROLE'] == "event_organiser" ? "":"disabled style='cursor:not-allowed'" ?> ><?= $currentEvent->event_description ?></textarea>
        </div>
        <!-- Handle save event -->
        <div class="form-save">
            <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
            <button class="w2ui-btn w2ui-action" id="save-settings-portal">Save Changes</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<script type="module">
    /* UPLOAD CARE */
    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
    // Add event listeners for badge and banner uploaders
    const badgeProviderNode = document.getElementById('badge-uploader');
    badgeProviderNode.addEventListener('change', event => {
        console.log("Badge uploaded");
        renderFiles(event.detail.allEntries.filter((file) => file.status === 'success'));
    });

    const bannerProviderNode = document.getElementById('banner-uploader');
    bannerProviderNode.addEventListener('change', event => {
        console.log("Banner uploaded");
        renderFiles(event.detail.allEntries.filter((file) => file.status === 'success'),true);
    });
    <?php endif; ?>

    // Function to render uploaded files
    function renderFiles(files,isBanner=false) {
        const renderedFiles = files.map(file => {
            // Update the lesson thumbnail source and alt attributes
            const lessonThumb = document.getElementById(`${isBanner ? 'banner':'badge'}-image`);

            // TEST
            console.log(lessonThumb);

            lessonThumb.src = file.cdnUrl + (isBanner ? '-/resize/1200x400/' : '-/resize/300x300/');
            lessonThumb.alt = file.fileInfo.originalFilename;

            if(isBanner){
                window.bannerImage = lessonThumb.src;
            }else{
                window.badgeImage = lessonThumb.src;
            }
        });
    }
    /* UPLOAD CARE */

    // Hide
    $("#settings-portal").hide();

    // Loader Container
    const portalLoaderContainer = document.getElementById('portal-loaderContainer');
    portalLoaderContainer.style.display = 'none';

    // --------------------------------------------------------------------------------------------------------------------
    // Instantiate W2UI elements here

    // $.ajax({
    //     url: API_URL + "api/v1/courses/get_course.php",
    //     method: "POST",
    //     headers: {
    //         "Access-Control-Allow-Origin": "*",
    //         "Content-Type": "application/json"
    //     },
    //     data: JSON.stringify({
    //         token: TOKEN,
    //         course_id: <?= $currentEvent->event_course ?>
    //     }),
    //     contentType: "application/json; charset=utf-8",
    //     dataType: "json",
    //     success: function(response) {
    //         console.log("Courses Retrieved Successfully");
    //         courses = response.data.map(course => ({ text: course.course_name, id: course.id , tee_data: course.tee_data}));

    //         // Instantiate default tee fields - and leave blank until course is selected
    //         // Men
    //         let menDefaultTees = new w2field('list', {
    //             el: document.querySelector("#men-defauult-tees"),
    //             items: ['Please select a course first'],
    //             match: 'contains',
    //             markSearch: true,
    //             onSelect(event) {
    //                 if(event.detail.item.id === womenDefaultTees.selected.id){
    //                     menDefaultTees.selected = {};
    //                     gimmeToast('Cannot select the same tee for both genders', 'error');
    //                 }
    //             }
    //         })
    //         // Women
    //         let womenDefaultTees = new w2field('list', {
    //             el: document.querySelector("#women-defauult-tees"),
    //             items: ['Please select a course first'],
    //             match: 'contains',
    //             markSearch: true,
    //             onSelect(event) {
    //                 if(event.detail.item.id === menDefaultTees.selected.id){
    //                     womenDefaultTees.selected = {};
    //                     gimmeToast('Cannot select the same tee for both genders', 'error');
    //                 }
    //             }
    //         })

    //         // Instantiate course select field
    //         new w2field('list', {
    //             el: query('input[type=list]')[1],
    //             items: courses,
    //             match: 'contains',
    //             markSearch: true,
    //             onSelect(event) {
    //                 const teeData = Object.keys(event.detail.item.tee_data.tees).map(tee => ({text: tee}));

    //                 // Toggle tee data on default tee fields
    //                 menDefaultTees.options.items = teeData;
    //                 womenDefaultTees.options.items = teeData;
    //             }
    //         })
    //     },
    //     error: function(error) {
    //         console.log("Error Retrieving Courses");
    //         console.log(error);
    //     }
    // });

    window.instantiateColorPicker = ()=>{
        new w2field({ el: query('#w2color')[0], type: 'color' })
    }

    function checkForImagesAndSave(){
        if(window.bannerImage && window.badgeImage){
            console.log("REQ 1")
            console.log(window.badgeImage)
            let data = {
                token:TOKEN,
                url_link: window.badgeImage,
                folder: "badge"
            };

            // Send AJAX post request to save file
            $.ajax({
                url:'../util/save_image.php',
                method: 'POST',
                data: JSON.stringify(data),
                headers: {
                    "Content-Type": "application/json"
                },
                success: function(badgeResponse) {
                    console.log(badgeResponse)
                    // TOAST SUCCESS HERE
                    let data = {
                        token:TOKEN,
                        url_link: window.bannerImage,
                        folder: "banner"
                    };

                    // Send AJAX post request to save file
                    $.ajax({
                        url: '../util/save_image.php',
                        method: 'POST',
                        data: JSON.stringify(data),
                        headers: {
                            "Content-Type": "application/json"
                        },
                        success: function(bannerResponse) {
                            // TOAST SUCCESS HERE && RELOAD
                            console.log("SUCCESS")
                            window.imagesSaved = {badge:badgeResponse.url,banner:bannerResponse.url};
                            saveSettingsPortal()
                        },
                        error: function(error) {
                            console.error('Error updating event');
                            gimmeToast("Could not update event",'error');
                            window.imagesSaved = {error:error};
                        }
                    });
                },
                error: function(error) {
                    console.error('Error updating event');
                    console.log(error)
                    gimmeToast("Could not update event",'error');
                    window.imagesSaved = {error:error};
                }
            });
        }else if(window.bannerImage && !window.badgeImage){
            console.log("REQ 2")
            let data = {
                token:TOKEN,
                url_link: window.bannerImage,
                folder: "banner"
            };

            // Send AJAX post request to save file
            $.ajax({
                url: '../util/save_image.php',
                method: 'POST',
                data: data,
                headers: {
                    "Content-Type": "application/json"
                },
                success: function(bannerResponse) {
                    console.log(bannerResponse)
                    window.imagesSaved = {badge:null,banner:bannerResponse.url};
                    saveSettingsPortal()
                },
                error: function(error) {
                    console.error('Error updating event');
                    gimmeToast("Could not update event",'error');
                    window.imagesSaved = {error:error};
                }
            });
        }else if(!window.bannerImage && window.badgeImage){
            console.log("REQ 3")
            let data = {
                token:TOKEN,
                url_link: window.badgeImage,
                folder: "badge"
            };

            // Send AJAX post request to save file
            $.ajax({
                url: '../util/save_image.php',
                method: 'POST',
                data: data,
                headers: {
                    "Content-Type": "application/json"
                },
                success: function(badgeResponse) {
                    console.log(badgeResponse)
                    window.imagesSaved = {badge:badgeResponse.url,banner:null};
                    saveSettingsPortal()
                },
                error: function(error) {
                    console.error('Error updating event');
                    gimmeToast("Could not update event",'error');
                    window.imagesSaved = {error:error};
                }
            });
        }else{
            window.imagesSaved = {badge:null,banner:null};
            saveSettingsPortal()
        }
    }

    function saveSettingsPortal() {
        const eventDescription = document.getElementById('event-description').value;
        const colorTheme = document.querySelector("#w2color").value;

        if (!eventDescription) {
            alert('All fields are required');
            return;
        }
        if (!colorTheme) {
            alert('All fields are required');
            return;
        }

        /*
        - First the image/s must be saved if one is set
        - Then form data is saved to the database
        - On success, refresh page
        - On error, display error
        */

        let imagesSaved = window.imagesSaved;
        console.log(imagesSaved)

        const data = {
            token: TOKEN,
            id: <?= $_POST['event_id'] ?>,
            event_description: eventDescription,
            event_banner: imagesSaved.banner ? imagesSaved.banner : null,
            event_badge: imagesSaved.badge ? imagesSaved.badge : null,
            event_colour: colorTheme
        };

        // Send AJAX post request to update_event.php
        $.ajax({
            url: API_URL + 'api/v1/event/update_event.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                console.log('Event updated successfully');
                gimmeToast("Event updated",'success');
            },
            error: function(error) {
                console.error(error);
                gimmeToast("Could not update event",'error');
            }
        });
    }

    <?php if($_SESSION['USER_ROLE'] == "event_organiser"): ?>
    document.getElementById('save-settings-portal').onclick = function() {
        checkForImagesAndSave();
    };
    <?php endif?>

</script>
