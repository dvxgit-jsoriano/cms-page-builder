<?php
session_start();
include_once("logger.php");

// Check if session filename is already generated
if (!isset($_SESSION['page_data_file'])) {
    // Generate a random filename like h4u35u92.json
    $randomSessionId = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
    $_SESSION['page_data_file'] = $randomSessionId . '.json';

    // Create initial page data
    $data = [
        "session" => $randomSessionId,
        "page" => [
            [
                "id" => 1,
                "name" => "slug-page-name",
                "block" => []
            ]
        ]
    ];

    // Convert to JSON
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);

    // Save to file (you can adjust path as needed)
    file_put_contents(__DIR__ . '/sessions/' . $_SESSION['page_data_file'], $jsonData);
}

// Output session filename for testing
echo "Your session file: " . $_SESSION['page_data_file'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CMS Page Builder</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body class="p-4 bg-gray-50">

<div class="flex m-4 border rounded">
    <!-- Left: Block Templates -->
    <div class="basis-1/4 p-4 border-r border-gray-400">
        <h2 class="text-lg font-bold mb-4">HTML Blocks</h2>
        <ul id="draggable-list" class="space-y-2">
          <li class="p-4 bg-white border rounded shadow hover:bg-gray-100 cursor-pointer" data-block-type="hero">Block Hero</li>
          <li class="p-4 bg-white border rounded shadow hover:bg-gray-100 cursor-pointer" data-block-type="banner">Block Banner</li>
          <li class="p-4 bg-white border rounded shadow hover:bg-gray-100 cursor-pointer" data-block-type="left-image">Block Left Image</li>
        </ul>
    </div>

    <!-- Right: Drop Canvas -->
    <div class="basis-3/4 p-4">
        <h2 class="text-lg font-bold mb-4">Page Layout</h2>
        <div id="sortable-list" class="min-h-[200px] border border-dashed border-gray-400 rounded bg-white relative">
            <!-- Blocks will be added here -->
            <div id="loading-overlay" class="hidden absolute inset-0 bg-white bg-opacity-70 z-50 flex justify-center items-center">
                <img src="https://i.imgur.com/llF5iyg.gif" alt="Loading..." class="w-16 h-16">
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-between">
  <div></div>
  <div>
    <button id="open-tab" class="w-full border rounded border-gray-200 bg-gray-100 hover:bg-gray-50 px-4 py-2">
      Open Contents to new tab
    </button>
    <button onclick="destroySession()" id="destroy-session" class="w-full border rounded border-gray-200 bg-gray-100 hover:bg-gray-50 px-4 py-2">
      Clear Data
    </button>
  </div>
  <div></div>
</div>

<!-- Bootstrap Modal for Block Editing -->
<div class="modal fade" id="blockEditModal" tabindex="-1" aria-labelledby="blockEditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="blockEditModalLabel">Edit Block</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="blockEditForm">
          <!-- Dynamic form fields will go here -->
          <div id="blockFormFields"></div>
          <input type="hidden" id="blockId" name="id">
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveBlockChanges">Save changes</button>
      </div>

    </div>
  </div>
</div>


<script>
    $(document).ready(function() {
        // Fetch the pageData on page load
        fetchPageData();
    });

    function fetchPageData() {
        $.ajax({
            url: 'get-page-data.php',  // This will fetch the page data from your server
            method: 'GET',
            success: function(response) {
                // Check if there's pageData in the response
                if (response && response.page && Array.isArray(response.page[0].block)) {
                    renderBlocks(response.page[0].block);  // Call render method with blocks data
                } else {
                    console.error("Invalid or missing page data.");
                }
            },
            error: function() {
                console.error("Error fetching page data.");
            }
        });
    }

    function renderBlocks(blocks) {
        const layoutContainer = document.getElementById('sortable-list');  // Assuming this is your layout container

        // Loop through each block in pageData and render based on type
        blocks.forEach(block => {
            const blockHTML = getBlockTemplateFromServer(block);  // Reusing the same block rendering function

            // Create a wrapper div for the block
            const wrapper = document.createElement('div');
            wrapper.innerHTML = blockHTML;
            layoutContainer.appendChild(wrapper.firstElementChild);  // Append the rendered block to the layout
        });
    }

    // Fake drag source - clone instead of move
    new Sortable(document.getElementById('draggable-list'), {
        group: {
        name: 'blocks',
        pull: 'clone', // copy instead of move
        put: false     // don't allow dropping here
        },
        sort: false
    });

    // Drop target
    new Sortable(document.getElementById('sortable-list'), {
    group: 'blocks',
    animation: 150,
    sort: true,
    onAdd: function (evt) {
        // Get block type BEFORE removing the item
        const type = evt.item.dataset.blockType;

        // Now safely remove the item
        evt.item.remove();

        // Show loading (optional)
        const loading = document.createElement('div');
        loading.textContent = "Loading...";
        loading.className = "text-center p-4 text-gray-600";
        evt.to.appendChild(loading);

        // AJAX to server to create block
        $.ajax({
            url: 'create-block.php',
            method: 'POST',
            data: { type: type },
            success: function(response) {
                console.log("Server response:", response);

                try {
                    const blockData = response;
                    console.log(blockData);

                    const blockHTML = getBlockTemplateFromServer(blockData);

                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = blockHTML;

                    // Remove loading before adding the block
                    loading.remove();

                    evt.to.appendChild(wrapper.firstElementChild);
                } catch (err) {
                    console.error("Error parsing server response:", err);
                    loading.remove(); // Important in case of error too
                    alert("Failed to create block.");
                }
            },
            error: function() {
                loading.remove();
                alert("Server error while creating block.");
            }
        });
    }

    });

    function getBlockTemplateFromServer(blockData) {
        // Check the type of block and return HTML accordingly
        switch (blockData.type) {
            case 'hero':
                return `
                    <section data-id="${blockData.id}" class="group relative">
                        <button class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-3 py-1 rounded hover:bg-opacity-70 transition hidden group-hover:block edit-btn">
                            Edit
                        </button>
                        <div class="p-6 bg-blue-100 rounded shadow">
                            <h1 class="text-2xl font-bold mb-2">${blockData.title}</h1>
                            <p class="text-gray-700">${blockData.description}</p>
                        </div>
                    </section>
                `;
            case 'banner':
                return `
                    <div data-id="${blockData.id}" class="p-4 bg-yellow-100 rounded shadow text-center">
                        <p class="font-semibold">${blockData.description}</p>
                    </div>
                `;
            case 'left-image':
                return `
                    <div data-id="${blockData.id}" class="flex items-center gap-4 p-4 bg-green-100 rounded shadow">
                        <img src="${blockData.imageUrl}" class="w-20 h-20 rounded" />
                        <div>
                            <h3 class="text-lg font-bold">${blockData.title}</h3>
                            <p class="text-sm text-gray-600">${blockData.description}</p>
                        </div>
                    </div>
                `;
            default:
                return `<div class="p-4 bg-red-100 rounded">Unknown block type</div>`;
        }
    }

    function openEditModal(blockData) {
        $('#blockId').val(blockData.id);  // Save block id for the backend

        let html = '';

        switch (blockData.type) {
            case 'hero':
                html = `
                    <div class="mb-3">
                        <label for="blockTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="blockTitle" name="title" value="${blockData.title}">
                    </div>
                    <div class="mb-3">
                        <label for="blockDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="blockDescription" name="description">${blockData.description}</textarea>
                    </div>
                `;
                break;

            case 'banner':
                html = `
                    <div class="mb-3">
                        <label for="blockDescription" class="form-label">Banner Text</label>
                        <textarea class="form-control" id="blockDescription" name="description">${blockData.description}</textarea>
                    </div>
                `;
                break;

            case 'left-image':
                html = `
                    <div class="mb-3">
                        <label for="blockTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="blockTitle" name="title" value="${blockData.title}">
                    </div>
                    <div class="mb-3">
                        <label for="blockDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="blockDescription" name="description">${blockData.description}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="blockImageUrl" class="form-label">Image URL</label>
                        <input type="text" class="form-control" id="blockImageUrl" name="imageUrl" value="${blockData.imageUrl}">
                    </div>
                `;
                break;

            default:
                html = `<p>Unknown block type.</p>`;
                break;
        }

        $('#blockFormFields').html(html);

        const modal = new bootstrap.Modal(document.getElementById('blockEditModal'));
        modal.show();
    }


</script>

<script>
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();

        let section = $(this).closest('section');
        let blockId = section.data('id');

        console.log('Block ID:', blockId);

        $.ajax({
            url: 'block.php?id=' + blockId,
            method: 'GET',
            success: function(response) {
                console.log('Fetched block:', response);

                // Open Bootstrap modal with fetched block data
                openEditModal(response);
            },
            error: function(xhr) {
                console.error('Error fetching block:', xhr);
                alert('Error fetching block data.');
            }
        });
    });


  function destroySession() {
    $.ajax({
        url: 'destroy-session.php',
        method: 'GET',
        success: function(response) {
            // Log the response for debugging
            console.log("Session destroyed:", response);

            // Optionally, show a message or refresh the page
            alert("Session has been destroyed. You can now create a new session.");
            location.reload(); // Optionally reload the page to reset the state
        },
        error: function(xhr) {
            console.error("Error destroying session:", xhr);
            alert("There was an error while destroying the session.");
        }
    });
  }
</script>

<script>
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();

        let section = $(this).closest('section');    // get the parent section
        let blockId = section.data('id');            // get the block id

        let overlay = $('#loading-overlay');
        let sortableList = $('#sortable-list');

        // Show loading overlay & dim background
        overlay.removeClass('hidden');
        sortableList.css('opacity', '0.5');

        // Add 1 second delay for testing
        setTimeout(function() {

            $.ajax({
                url: 'block.php?id=' + blockId,
                method: 'GET',
                success: function(response) {
                    console.log(response);

                    section.find('h1').text(response.title);
                    section.find('p').text(response.description);
                },
                error: function(xhr) {
                    console.error('Error fetching block:', xhr);
                },
                complete: function() {
                    // Restore after AJAX finishes
                    overlay.addClass('hidden');
                    sortableList.css('opacity', '1');
                }
            });

        }, 1000);
    });
</script>

<script>
    document.getElementById('open-tab').addEventListener('click', () => {
    const layoutContent = document.getElementById('sortable-list').innerHTML;

    const html = 
      '<!DOCTYPE html>' +
      '<html lang="en">' +
      '<head>' +
      '<meta charset="UTF-8">' +
      '<title>Exported Layout</title>' +
      '<script src="https://cdn.tailwindcss.com"><\/script>' +
      '</head>' +
      '<body class="bg-gray-50">' +
      layoutContent +
      '</body>' +
      '</html>';

    const newWindow = window.open('', '_blank');
    newWindow.document.write(html);
    newWindow.document.close();
  });
</script>

</body>
</html>