/*
  Requirement: Populate the assignment detail page and discussion forum.

  Instructions:
  1. Link this file to `details.html` using:
     <script src="details.js" defer></script>

  2. In `details.html`, add the following IDs:
     - To the <h1>: `id="assignment-title"`
     - To the "Due" <p>: `id="assignment-due-date"`
     - To the "Description" <p>: `id="assignment-description"`
     - To the "Attached Files" <ul>: `id="assignment-files-list"`
     - To the <div> for comments: `id="comment-list"`
     - To the "Add a Comment" <form>: `id="comment-form"`
     - To the <textarea>: `id="new-comment-text"`

  3. Implement the TODOs below.
*/
// --- Element Selections ---
// TODO: Select all the elements you added IDs for in step 2.

const assignmentTitleEl = document.getElementById("assignment-title");
const assignmentDueDateEl = document.getElementById("assignment-due-date");
const assignmentDescriptionEl = document.getElementById("assignment-description");
const assignmentFilesListEl = document.getElementById("assignment-files-list");
const commentListEl = document.getElementById("comment-list");
const commentFormEl = document.getElementById("comment-form");
const newCommentTextEl = document.getElementById("new-comment-text");


// --- Global Data Store ---
// These will hold the data related to *this* assignment.
let currentAssignmentId = null;
let currentComments = [];

// --- Element Selections ---
// TODO: Select all the elements you added IDs for in step 2.

// --- Functions ---

/**
 * TODO: Implement the getAssignmentIdFromURL function.
 * It should:
 * 1. Get the query string from `window.location.search`.
 * 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
 * 3. Return the id.
 */
function getAssignmentIdFromURL() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const id = urlParams.get('id');
  return id;
}

/**
 * TODO: Implement the renderAssignmentDetails function.
 * It takes one assignment object.
 * It should:
 * 1. Set the `textContent` of `assignmentTitle` to the assignment's title.
 * 2. Set the `textContent` of `assignmentDueDate` to "Due: " + assignment's dueDate.
 * 3. Set the `textContent` of `assignmentDescription`.
 * 4. Clear `assignmentFilesList` and then create and append
 * `<li><a href="#">...</a></li>` for each file in the assignment's 'files' array.
 */
function renderAssignmentDetails(assignment) {
assignmentTitleEl.textContent = assignment.title;
assignmentDueDateEl.textContent = `Due: ${assignment.dueDate}`;
assignmentDescriptionEl.textContent = assignment.description;
assignmentFilesListEl.innerHTML = '';

assignment.files.forEach(file => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.href = file;
    a.textContent = file;
    li.appendChild(a);
    assignmentFilesListEl.appendChild(li);
});
}

/**
 * TODO: Implement the createCommentArticle function.
 * It takes one comment object {author, text}.
 * It should return an <article> element matching the structure in `details.html`.
 */
function createCommentArticle(comment) {
    const article = document.createElement('article');
    article.classList.add('comment');

    const p = document.createElement('p');
    p.textContent = comment.text;
    article.appendChild(p);
    const footer = document.createElement('footer');
    footer.textContent = `Posted by: ${comment.author}`;
    article.appendChild(footer);

    return article;
}


/**
 * TODO: Implement the renderComments function.
 * It should:
 * 1. Clear the `commentList`.
 * 2. Loop through the global `currentComments` array.
 * 3. For each comment, call `createCommentArticle()`, and
 * append the resulting <article> to `commentList`.
 */
function renderComments() {
commentListEl.innerHTML = '';
 currentComments.forEach(comment => {
   const commentArticle = createCommentArticle(comment);
        commentListEl.appendChild(commentArticle);
    });

}

/**
 * TODO: Implement the handleAddComment function.
 * This is the event handler for the `commentForm` 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the text from `newCommentText.value`.
 * 3. If the text is empty, return.
 * 4. Create a new comment object: { author: 'Student', text: commentText }
 * (For this exercise, 'Student' is a fine hardcoded author).
 * 5. Add the new comment to the global `currentComments` array (in-memory only).
 * 6. Call `renderComments()` to refresh the list.
 * 7. Clear the `newCommentText` textarea.
 */
function handleAddComment(event) {
  event.preventDefault();
  const commentText = newCommentTextEl.value;
  if (!commentText.trim()) return;
  const newComment = { author: 'Student', text: commentText };
  currentComments.push(newComment);
  renderComments();
  newCommentTextEl.value = '';
}


/**
 * TODO: Implement an `initializePage` function.
 * This function needs to be 'async'.
 * It should:
 * 1. Get the `currentAssignmentId` by calling `getAssignmentIdFromURL()`.
 * 2. If no ID is found, display an error and stop.
 * 3. `fetch` both 'assignments.json' and 'comments.json' (you can use `Promise.all`).
 * 4. Find the correct assignment from the assignments array using the `currentAssignmentId`.
 * 5. Get the correct comments array from the comments object using the `currentAssignmentId`.
 * Store this in the global `currentComments` variable.
 * 6. If the assignment is found:
 * - Call `renderAssignmentDetails()` with the assignment object.
 * - Call `renderComments()` to show the initial comments.
 * - Add the 'submit' event listener to `commentForm` (calls `handleAddComment`).
 * 7. If the assignment is not found, display an error.
 */
 async function initializePage() {
  currentAssignmentId = getAssignmentIdFromURL();

  if (!currentAssignmentId) {
    alert('Error: No assignment ID found in URL.');
    return;
  }

  try {
    const [assignmentsResponse, commentsResponse] = await Promise.all([
  fetch('./api/assignments.json'),
  fetch('./api/comments.json')
    ]);

    const assignments = await assignmentsResponse.json();
    const comments = await commentsResponse.json();

    const assignment = assignments.find(
      ass => ass.id === currentAssignmentId
    );

    currentComments = comments[currentAssignmentId] || [];

    if (assignment) {
      renderAssignmentDetails(assignment);
      renderComments();
      commentFormEl.addEventListener('submit', handleAddComment);
    } else {
      alert('Error: Assignment not found.');
    }

  } catch (error) {
    alert('Error loading data: ' + error.message);
  }
}

// --- Initial Page Load ---
initializePage();
