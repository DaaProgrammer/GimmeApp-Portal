<?php 
    $pageTitle = "Rule Book";
    require_once 'session/session.php';

    // Get all articles
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $_ENV['API_URL'].'api/v1/articles/retreive_articles.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "token":"'.$_COOKIE['jwt'].'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $articles = json_decode(curl_exec($curl));

    curl_close($curl);
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
                    <div class="card-header pb-3 px-3">
                        <p class="mb-0">Manage rule books and view key rule data.</p>

                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>

                    <div id="users" class="w-100 px-6">
                        <div id="grid" style="width: 100%; max-width:1000px; height: 450px; overflow: hidden;"></div>
                        <div class="actions" style="justify-content:left;">
                            <button class="w2ui-btn w2ui-btn-blue" id="createModalBtn">
                                Create New Rule Book
                            </button>
                        </div>
                    </div>

                <!-- Create/Edit Form -->
                <div id="create-rulebook-container" style="display: none; padding: 20px">
                    <div rel="title">
                        <h3 id="rule-container-title">Create a Rule Book</h3>
                    </div>
                    <div rel="body" style="padding: 10px; line-height: 150%">
                        <div id="toolbar"></div>
                        <section>
                            <h4>Rule Book Details</h4>
                            <div class="w2ui-field">
                                <label for="rule-title">Rule Book Title</label>
                                <div><input id="rule-title" name="rule-title" class="w2ui-input" style="width: 300px" type="text" tabindex="1"></div>
                            </div>
                            <div class="w2ui-field">
                                <label for="rule-content">Rule Book Content</label>
                                <div><div id="rule-content" class="w2ui-input" style="width: 100%;min-height:250px" type="text"></div></div>
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
                            <img id="rule-image-loaded" src="" style="width: 300px; height: 300px; border-radius: 10px; display: none;"/>
                        </div>

                    </div>
                    <div rel="buttons">
                        <button class="w2ui-btn w2ui-action" id="close-form-btn">Close</button>
                        <button class="w2ui-btn w2ui-btn-blue" id="create-rulebook-button">Confirm</button>
                    </div>
                </div>
                <!-- END Create/Edit Form -->
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
    loaderContainer.style.display = 'flex';
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

// Create Form
new w2field('list', {
    el: document.querySelector("#scoring"),
    items: ['strokeplay','stableford'],
    match: 'contains',
    markSearch: true,
    onSelect(event) {
        console.log('Selected:', event.detail.item)
    }
})
// End Create Form

let grid = new w2grid({
    name: 'grid',
    box: '#grid',
    header: 'Game Rule Books',
    reorderRows: false,
    show: {
        header: true,
        footer: true,
        toolbar: true,
        lineNumbers: true
    },
    columns: [
        { field: 'image', text: 'Image', size: '150px', sortable: true},
        { field: 'title', text: 'Title', size: '200px', sortable: true, clipboardCopy: true},
        { field: 'contents', text: 'Content', size: '300px', sortable: true, clipboardCopy: true},
        { field: 'created_at', text: 'Created', size: '150px', sortable: true, clipboardCopy: true},
        { field: 'actions', text: 'Actions', size: '200px'}
    ],
    searches: [
        { type: 'int',  field: 'id', label: 'ID' },
        { field: 'title', label: 'Title' },
        { field: 'contents', label: 'Content' },
        { type: 'date', field: 'created_at', label: 'Created At' },
    ],
    onExpand(event) {
        query('#'+event.detail.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>')
    }
});

function populateTable(){
    // Clear existing records
    const books = <?= json_encode($articles->articles) ?> 
    console.log(books)
    grid.clear();
    loaderContainer.style.display = 'none';

    // Process and add new records
    books.forEach(function(book) {
        grid.add({
            recid:                      book.id,
            image:                      book.image_path,
            title:                      book.title,
            contents:                   book.contents,
            created_at:                 book.created_at,
            actions:                    `
            <form method="POST" action="/update-rulebook/">
                <input type="hidden" name="article_id" id="article_id" value='${book.id}'/>
                <input type="hidden" name="article_image_path" id="article_image_path" value='${book.image_path}'/>
                <input type="hidden" name="article_title" id="article_title" value='${book.title}'/>
                <input type="hidden" name="article_contents" id="article_contents" value='${book.contents}'/>
                <input type="submit" value="Edit" class="w2ui-btn w2ui-btn-blue" style="width:100%;padding:5px;color:white;background-color:#00b240;border:none;border-radius:12px;"/>
            </form>
            `
        });
    });

    grid.refresh();
}
populateTable();

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
function createRuleBook(){
    const title = document.getElementById('rule-title');
    const content = document.getElementById('rule-content');
    const image = window.ruleImage ? window.ruleImage : null;

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
    if(image == null){
        gimmeToast("Please upload an image","error");
        return;
    }
    let imageUploadRes = uploadImage();

    console.log(window.imagesSaved.hasOwnProperty("rule"));
    if(window.imagesSaved.hasOwnProperty("rule")){
        const data = {
            token:TOKEN,
            title: title.value,
            contents: quill.root.innerHTML,
            image_path: window.imagesSaved.rule
        }

        document.querySelector('#create-rulebook-button').disabled = true;
        document.querySelector('#create-rulebook-button').innerHTML = "Processing...";
        document.querySelector('#close-form-btn').disabled = true;

        // Send AJAX post request to save file
        $.ajax({
            url: API_URL+'api/v1/articles/create_rulebook.php',
            method: 'POST',
            data: JSON.stringify(data),
            headers: {
                "Content-Type": "application/json"
            },
            success: function(ruleResponse) {
                gimmeToast("Rulebook created successfully",'success');
                window.location.reload();
            },
            error: function(error) {
                console.log(error)
                gimmeToast("Could not create event",'error');

                document.querySelector('#create-rulebook-button').disabled = false;
                document.querySelector('#create-rulebook-button').innerHTML = "Confirm";
                document.querySelector('#close-form-btn').disabled = false;
            }
        });
    }else{
        console.log("Image not uploaded")
        gimmeToast("Could not create event",'error');

        document.querySelector('#create-rulebook-button').disabled = false;
        document.querySelector('#create-rulebook-button').innerHTML = "Confirm";
        document.querySelector('#close-form-btn').disabled = false;
    }
}

function closeEventForm(){
    $('#create-rulebook-container').hide(500);
    $('#createModalBtn').show(500);
    $('#grid').show(500);
}

document.querySelector("#createModalBtn").addEventListener('click', function() {
    // Add your event handling code here
    $('#create-rulebook-container').show(500);
    $('#createModalBtn').hide(500);
    $('#grid').hide(500);
});
document.querySelector("#close-form-btn").addEventListener("click", closeEventForm);
document.querySelector('#create-rulebook-button').addEventListener("click",function(){
    createRuleBook();
});
</script>
</body>
</html>
