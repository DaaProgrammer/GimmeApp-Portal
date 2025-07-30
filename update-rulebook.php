<?php
    $pageTitle = "Update Rule Book";
    require_once 'session/session.php';

    if(!isset($_POST['article_id']) || !isset($_POST['article_image_path']) || !isset($_POST['article_title']) || !isset($_POST['article_contents'])){
        echo <<<EOD
        <h2>Error 01:</h2>
        <p>Sorry, we could not find the article that you want to update. Please try again later or contact your administrator for support.</p><br/>
        <p>You will be redirected soon. Please wait...</p>
        <script>
            window.location.href = "/
        </script>
        EOD;
    }
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

.actions{
    margin-top: 20px;
    display:flex;
    justify-content:right;
    align-items:center;
}
#event-registration-deadline{
    cursor: not-allowed;
    font-weight:bold;
}
.event-type-team{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #d7d7d7;
}
.event-type-individual{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #00b240;
    color:white;
}
.event-status-pending{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #d7d7d7;
    font-weight:bold;
}
.event-status-active{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color: #ffa041;
    color: black;
    font-weight:bold;
}
.event-status-complete{
    padding: 5px;
    text-align: center;
    border-radius: 12px;
    background-color:#00b240;
    color:black;
    font-weight:bold;
}
.modal{
    background-color: #000000b8;
}
</style>    
    <!-- Navbar -->
        <?php include 'partials/top-nav.php'; ?>
    <!-- End Navbar -->
    <script type="module">
        import * as LR from "https://cdn.jsdelivr.net/npm/@uploadcare/blocks@0.35.2/web/lr-file-uploader-regular.min.js";
        LR.registerBlocks(LR);
    </script>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
              <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3">Events</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="users" class="w-100 px-6">
                        <!-- Create/Edit Form -->
                        <div id="create-rulebook-container">
                            <div rel="title">
                                <h3 id="rule-container-title">Update Rule Book</h3>
                            </div>
                            <div rel="body" style="padding: 10px; line-height: 150%">
                                <div id="toolbar"></div>
                                <section>
                                    <div class="w2ui-field">
                                        <label for="rule-title">Rule Book Title</label>
                                        <div><input id="rule-title" name="rule-title" class="w2ui-input" style="width: 300px" type="text" tabindex="1" value="<?= $_POST['article_title'] ?>"></div>
                                    </div>
                                    <div class="w2ui-field">
                                        <label for="rule-content">Rule Book Content</label>
                                        <div>
                                            <div id="rule-content" style="width: 100%;min-height:250px" type="text">
                                                <?= $_POST['article_contents'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <div class="w2ui-field form-input">
                                    <label for="uploadcare-rule-uploader">Upload Image</label>
                                    <div id="uploadcare-rule-uploader">
                                        <lr-config
                                            ctx-name="rule-uploader"
                                            pubkey="f80a9d210d3998ceb907"
                                            max-local-file-size-bytes="500000000"
                                            multiple="false"
                                            source-list="local, url, gdrive"
                                            crop-preset="1:1"
                                            use-cloud-image-editor="true"
                                            image-shrink="300x300 center"
                                            img-only="true"
                                            store="false"
                                            ></lr-config>
                                        <lr-file-uploader-regular
                                            css-src="https://cdn.jsdelivr.net/npm/@uploadcare/blocks@0.35.2/web/lr-file-uploader-regular.min.css"
                                            ctx-name="rule-uploader"
                                            class="my-config"
                                        >
                                        </lr-file-uploader-regular>

                                        <lr-upload-ctx-provider
                                            ctx-name="rule-uploader"
                                            id="rule-uploader"
                                        ></lr-upload-ctx-provider>
                                    </div>
                                    <?php
                                        $imagePath = $_POST['article_image_path'];
                                        if($imagePath){
                                            echo "<div id='default-rule-img--container'>";
                                            echo "<img id='rule-image-loaded' src='$imagePath' style='width: 300px; height: 300px; border-radius: 10px;'/>";  
                                            echo "</div>";
                                        } else {
                                            echo "<img id='rule-image-loaded' src='/assets/img/default/rule.png' style='width: 300px; height: 300px; border-radius: 10px;'/>";
                                        }
                                    ?>

                                </div>

                            </div>
                        </div>
                        <div class="actions" style="justify-content: left">
                            <button class="w2ui-btn w2ui-btn-red" id="deleteModalBtn">
                                Delete
                            </button>
                            <button class="w2ui-btn w2ui-btn-blue" id="updateModalBtn">
                                Update
                            </button>
                        </div>
                    </div>

                <!-- END Create/Edit Form -->
                </div>
              </div> 
            </div>
        </div> 

        <div class="modal" id="delete-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-modal-title">Are you sure?</h5>
                </div>
                <div class="modal-body">
                    <p>Are you sure you wish to delete this rule book? Once deleted, it cannot be recovered!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="dismiss-rule-modal-btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="delete-rule-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>

        <?php include 'partials/footer.php'; ?>
    </div>
</main>

<script type='module' src='../util/util.js'></script>
<script type="module">
    const API_URL = "<?php echo $_ENV['API_URL']; ?>";
    const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";  

    // Loader Container
    const loaderContainer = document.getElementById('loaderContainer');
    loaderContainer.style.display = 'none';
    const usersContainer = document.getElementById('users');

    /* UPLOAD CARE */
    // Add event listeners for badge and banner uploaders
    const ruleProviderNode = document.getElementById('rule-uploader');
    ruleProviderNode.addEventListener('change', event => {
        console.log("Rule Image uploaded");
        renderFiles(event.detail.allEntries.filter((file) => file.status === 'success'));
    });

    // Function to render uploaded files
    function renderFiles(files,isBanner=false) {
        const renderedFiles = files.map(file => {
            // Update the lesson thumbnail source and alt attributes
            const ruleThumb = document.getElementById('rule-image-loaded');

            ruleThumb.src = file.cdnUrl + '-/resize/300x300/';
            ruleThumb.alt = file.fileInfo.originalFilename;
            ruleThumb.style.display = "block";

            window.ruleImage = ruleThumb.src;
        });
    }
    /* UPLOAD CARE */

    /* QUILL EDITOR */
    const quill = new Quill('#rule-content', {
        modules: {
            toolbar: [
            [{ header: [1, 2, false] }],
            ['bold', 'italic', 'underline'],
            ],
        },
        placeholder: 'The content of the rulebook goes here...',
        theme: 'snow', // or 'bubble'
    });
    /* END QUILL EDITOR */

function uploadImage(){
    let data = {
        token:TOKEN,
        url_link: window.ruleImage,
        folder: "rules"
    };
    // Send AJAX post request to save file
    $.ajax({
        url: '../util/save_image.php',
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
            "Content-Type": "application/json"
        },
        success: function(ruleResponse) {
            window.imagesSaved = {rule:ruleResponse.url};
            return true;
        },
        error: function(error) {
            gimmeToast("Could not update event",'error');
            window.imagesSaved = {error:error};
            return false;
        }
    });
}
async function updateRuleBook(){
    const title = document.getElementById('rule-title');
    const content = document.getElementById('rule-content');
    const image = window.ruleImage ? window.ruleImage : null;

    console.log(quill.root.innerHTML)

    // Check all fields are filled
    if(title.value == ""){
        gimmeToast("Please enter a title","error");
        title.style.border = "1px solid red";
        return;
    }
    if(quill.root.innerHTML == ""){
        gimmeToast("Please enter content","error");
        content.style.border = "1px solid red";
        return;
    }
    if(image != null){
        let imageUploadRes = await uploadImage();
    }

    document.querySelector('#updateModalBtn').disabled = true;
    document.querySelector('#updateModalBtn').innerHTML = 'Processing...';
    document.querySelector('#deleteModalBtn').disabled = true;

    const data = {
        token:TOKEN,
        article_id: <?= $_POST['article_id'] ?>,
        title: title.value,
        contents: quill.root.innerHTML,
        image_path: image != null ? window.imagesSaved.rule : null
    }

    // Send AJAX post request to save file
    $.ajax({
        url: API_URL+'api/v1/articles/update_rulebook.php',
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
            "Content-Type": "application/json"
        },
        success: function(ruleResponse) {
            gimmeToast("Rulebook updated successfully",'success');
            // window.location.reload();
        },
        error: function(error) {
            console.log(error)
            gimmeToast("Could not update event",'error');

            document.querySelector('#updateModalBtn').disabled = false;
            document.querySelector('#updateModalBtn').innerHTML = 'Update';
            document.querySelector('#deleteModalBtn').disabled = false;
        }
    });
}
function deleteRuleBook(){
    const data = {
        token:TOKEN,
        article_id: <?= $_POST['article_id'] ?>,
    }

    document.querySelector('#updateModalBtn').disabled = true;
    document.querySelector('#deleteModalBtn').innerHTML = 'Processing...';
    document.querySelector('#deleteModalBtn').disabled = true;

    // Send AJAX post request to save file
    $.ajax({
        url: API_URL+'api/v1/articles/delete_rulebook.php',
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
            "Content-Type": "application/json"
        },
        success: function(ruleResponse) {
            gimmeToast("Rulebook deleted successfully",'success');
            window.location.href = '/rules';
        },
        error: function(error) {
            console.log(error)
            gimmeToast("Could not delete event",'error');
            document.querySelector('#updateModalBtn').disabled = false;
            document.querySelector('#deleteModalBtn').innerHTML = 'Delete';
            document.querySelector('#deleteModalBtn').disabled = false;
        }
    });
}

function closeEventForm(){
    $('#create-rulebook-container').hide(500);
    $('#createModalBtn').show(500);
    $('#grid').show(500);
}

document.querySelector('#updateModalBtn').addEventListener("click",function(){
    updateRuleBook();
});
document.querySelector('#deleteModalBtn').addEventListener("click",function(){
    $('#delete-modal').show(500);
});
document.querySelector('#delete-rule-btn').addEventListener("click",function(){
    deleteRuleBook();
});
//dismiss-rule-modal-btn
document.querySelector('#dismiss-rule-modal-btn').addEventListener("click",function(){
    $('#delete-modal').hide(500);
});
</script>
</body>
</html>
