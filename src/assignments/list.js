/*
  Requirement: Populate the "Course Assignments" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="assignment-list-section"` to the
     <section> element that will contain the assignment articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
const listSection = document.querySelector('#assignment-list-section');

// --- Functions ---

/**
 * TODO: Implement the createAssignmentArticle function.
 * It takes one assignment object {id, title, dueDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * The "View Details" link's `href` MUST be set to `details.html?id=${id}`.
 * This is how the detail page will know which assignment to load.
 */
function createAssignmentArticle(assignment) {
const article = document.createElement('article');
const h2 = document.createElement('h2');
h2.textContent = assignment.title;
article.appendChild(h2);
const pDue = document.createElement('p');
pDue.textContent = `Due: ${assignment.dueDate}`;
article.appendChild(pDue);
const pDesc = document.createElement('p');
pDesc.textContent = assignment.description;
article.appendChild(pDesc);
const a = document.createElement('a');
a.href = `details.html?id=${assignment.id}`;
a.textContent = 'View Details';
article.appendChild(a);
return article;
}

/**
 * TODO: Implement the loadAssignments function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the assignments array. For each assignment:
 * - Call `createAssignmentArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadAssignments() {
  try{
  const response = await fetch('assignments.json');
  const assignments = await response.json();
  listSection.innerHTML = '';
  for (const assignment of assignments) {
  const article = createAssignmentArticle(assignment);
  listSection.appendChild(article);
  }
}catch(error)
{
console.error('Error loading assignments:', error);
}

}

// --- Initial Page Load ---
// Call the function to populate the page.
loadAssignments();
