<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestion Modules, Cours et Tests (QCM) - Plateforme Éducative</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      margin: 0;
      padding: 20px;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
      color: #333;
    }
    h1, h2, h3 {
      color: #1e2b4d;
    }
    nav {
      margin-bottom: 20px;
      display: flex;
      gap: 15px;
    }
    nav button {
      background: #4a90e2;
      border: none;
      border-radius: 6px;
      color: white;
      padding: 10px 20px;
      font-size: 15px;
      cursor: pointer;
      transition: background 0.3s;
    }
    nav button:hover, nav button.active {
      background: #357abd;
    }
    section {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    ul {
      list-style-type: none;
      padding-left: 0;
    }
    li {
      margin-bottom: 12px;
      border-bottom: 1px solid #e0e7f1;
      padding-bottom: 8px;
      cursor: pointer;
    }
    li:hover {
      background-color: #f0f4fc;
    }
    .module-name {
      font-weight: 600;
      font-size: 18px;
    }
    .course-title, .quiz-title {
      margin-left: 12px;
      font-size: 16px;
      color: #555;
    }
    .section-subtitle {
      margin-top: 20px;
      font-weight: 600;
      font-size: 17px;
      border-bottom: 2px solid #4a90e2;
      padding-bottom: 5px;
      margin-bottom: 10px;
    }
    .test-list {
      margin-top: 20px;
    }
    .test-item {
      background: #e7f0fd;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 12px;
      border-left: 6px solid #4a90e2;
    }
    .test-item h3 {
      margin: 0 0 6px 0;
    }
    .test-item small {
      color: #444;
    }
    .scroll-container {
      max-height: 400px;
      overflow-y: auto;
      padding-right: 8px;
    }
    button, input[type=submit] {
      background: #4a90e2;
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 15px;
      transition: background 0.3s;
    }
    button:hover, input[type=submit]:hover {
      background: #357abd;
    }
    .btn-secondary {
      background: #ccc;
      color: #333;
      margin-left: 15px;
    }
    .btn-danger {
      background: #e74c3c;
      color: white;
    }
  </style>
</head>
<body>
  <h1>Plateforme éducative - Modules, Cours et Tests (QCM)</h1>

  <nav role="navigation" aria-label="Navigation principale">
    <button id="btnModules" class="active" aria-controls="modulesSection" aria-selected="true">Modules</button>
    <button id="btnOther" aria-controls="otherSection" aria-selected="false">Autre (exemple)</button>
  </nav>

  <section id="modulesSection" aria-live="polite" aria-atomic="true">
    <h2>Mes Modules</h2>
    <div id="modulesContainer" class="scroll-container" tabindex="0">
      <!-- Modules chargés -->
    </div>

    <div id="moduleDetails" style="margin-top:25px; display:none;">
      <h2 id="moduleTitle"></h2>
      <div>
        <h3 class="section-subtitle">Cours</h3>
        <ul id="coursesList" tabindex="0" aria-label="Liste des cours du module"></ul>
      </div>
      <div>
        <h3 class="section-subtitle">Tests (QCM)</h3>
        <ul id="quizzesList" tabindex="0" aria-label="Liste des tests du module"></ul>
      </div>
      <button id="btnCreateTest" style="display:none;">Créer un nouveau test</button>
    </div>

    <div id="testFormContainer" style="display:none; margin-top:30px;">
      <h2>Créer / Éditer un Test</h2>
      <form id="testForm" aria-label="Formulaire de création de test">
        <input type="hidden" id="hiddenModuleId" name="module_id" />
        <label for="testTitle">Titre du test</label>
        <input type="text" id="testTitle" name="test_title" placeholder="Ex: Test de Chapitre 1" required aria-required="true" />

        <label><input type="checkbox" id="testVisible" name="test_visible" /> Rendre ce test visible aux élèves</label>

        <div id="questionsArea" style="margin-top: 20px;">
          <h3>Questions</h3>
          <div id="questionsList" aria-live="polite" aria-relevant="additions" tabindex="0"></div>
          <button type="button" id="btnAddQuestion">Ajouter une question</button>
        </div>

        <input type="hidden" name="questions_json" id="questionsJson" />

        <button type="submit" style="margin-top:20px;">Enregistrer le test</button>
        <button type="button" id="btnCancelTest" class="btn-secondary">Annuler</button>
      </form>
    </div>
  </section>

  <section id="otherSection" style="display:none;">
    <h2>Autre contenu (exemple)</h2>
    <p>Cette section est un exemple pour autre contenu.</p>
  </section>

<?php
// Connexion MySQL et configuration
$host = '127.0.0.1';
$db = 'student_five';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];
try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  echo "<p>Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()) . "</p>";
  exit;
}

// Simulation utilisateur connecté (à adapter selon authentification réelle)
$currentUserId = 1;
$currentUserRole = 1; // 0=élève, 1=professeur, 2=admin

// POST: Création test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_test') {
  header('Content-Type: application/json; charset=utf-8');
  $title = trim($_POST['test_title'] ?? '');
  $module_id = intval($_POST['module_id'] ?? 0);
  $visible = isset($_POST['test_visible']) && $_POST['test_visible'] === 'on' ? 1 : 0;
  $questionsJson = $_POST['questions_json'] ?? '[]';

  // Vérification rôle
  if (!in_array($currentUserRole, [1, 2])) {
    echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas le droit de créer un test.']);
    exit;
  }

  if ($title && $module_id && $questionsJson) {
    // Si admin, autorise tous modules, sinon vérifier module professeur
    if ($currentUserRole === 1) {
      $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM professor_modules WHERE professor_id=? AND module_id=?');
      $stmtCheck->execute([$currentUserId, $module_id]);
      if ($stmtCheck->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez créer un test que pour vos modules.']);
        exit;
      }
    }
    $stmtInsert = $pdo->prepare('INSERT INTO quizzes (module_id, professor_id, title, questions) VALUES (?, ?, ?, ?)');
    $profId = ($currentUserRole === 2) ? ($currentUserId) : $currentUserId; // admin peut utiliser son id, ou adapter
    $stmtInsert->execute([$module_id, $profId, $title, $questionsJson]);
    echo json_encode(['success' => true]);
    exit;
  }
  echo json_encode(['success' => false, 'message' => 'Données invalides.']);
  exit;
}

// GET: Récupérer modules + cours + tests selon rôle
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_modules') {
  header('Content-Type: application/json; charset=utf-8');
  if ($currentUserRole === 2) {
    // Admin: tous modules
    $stmt = $pdo->query('SELECT module_id, module_name FROM modules ORDER BY module_name');
    $modules = $stmt->fetchAll();
  } else {
    // Prof: seulement modules liés
    $stmt = $pdo->prepare('
      SELECT m.module_id, m.module_name
      FROM modules m
      JOIN professor_modules pm ON pm.module_id = m.module_id
      WHERE pm.professor_id = ?
      ORDER BY m.module_name
    ');
    $stmt->execute([$currentUserId]);
    $modules = $stmt->fetchAll();
  }

  foreach ($modules as &$mod) {
    $stmtC = $pdo->prepare('SELECT course_id, title FROM courses WHERE module_id = ? ORDER BY created_at DESC');
    $stmtC->execute([$mod['module_id']]);
    $mod['courses'] = $stmtC->fetchAll();

    $stmtQ = $pdo->prepare('SELECT quiz_id, title, created_at FROM quizzes WHERE module_id = ? ');
    if ($currentUserRole === 2) {
      $stmtQ->execute([$mod['module_id']]);
    } else {
      $stmtQ = $pdo->prepare('SELECT quiz_id, title, created_at FROM quizzes WHERE module_id = ? AND professor_id = ?');
      $stmtQ->execute([$mod['module_id'], $currentUserId]);
    }
    $mod['quizzes'] = $stmtQ->fetchAll();
  }
  echo json_encode($modules);
  exit;
}
?>
<script>
  // Navigation
  const btnModules = document.getElementById('btnModules');
  const btnOther = document.getElementById('btnOther');
  const modulesSection = document.getElementById('modulesSection');
  const otherSection = document.getElementById('otherSection');

  btnModules.addEventListener('click', () => {
    btnModules.classList.add('active');
    btnOther.classList.remove('active');
    modulesSection.style.display = 'block';
    otherSection.style.display = 'none';
    btnModules.setAttribute('aria-selected', 'true');
    btnOther.setAttribute('aria-selected', 'false');
  });
  btnOther.addEventListener('click', () => {
    btnOther.classList.add('active');
    btnModules.classList.remove('active');
    modulesSection.style.display = 'none';
    otherSection.style.display = 'block';
    btnOther.setAttribute('aria-selected', 'true');
    btnModules.setAttribute('aria-selected', 'false');
  });

  const modulesContainer = document.getElementById('modulesContainer');
  const moduleDetails = document.getElementById('moduleDetails');
  const moduleTitle = document.getElementById('moduleTitle');
  const coursesList = document.getElementById('coursesList');
  const quizzesList = document.getElementById('quizzesList');

  const btnCreateTest = document.getElementById('btnCreateTest');
  const testFormContainer = document.getElementById('testFormContainer');
  const testForm = document.getElementById('testForm');
  const hiddenModuleId = document.getElementById('hiddenModuleId');
  const testTitle = document.getElementById('testTitle');
  const testVisible = document.getElementById('testVisible');
  const questionsList = document.getElementById('questionsList');
  const btnAddQuestion = document.getElementById('btnAddQuestion');
  const questionsJsonInput = document.getElementById('questionsJson');
  const btnCancelTest = document.getElementById('btnCancelTest');

  // Variables 'from PHP' indicating role to control UI (to be dynamically set)
  const currentUserRole = <?= json_encode(intval($currentUserRole)) ?>;
  const ROLE_ADMIN = 2;
  const ROLE_PROF = 1;

  let currentModules = [];
  let selectedModule = null;

  async function loadModules() {
    modulesContainer.innerHTML = 'Chargement...';
    try {
      const res = await fetch('educational_platform_qcm.php?action=get_modules');
      const modules = await res.json();
      currentModules = modules;
      renderModulesList(modules);
    } catch(e) {
      modulesContainer.textContent = 'Erreur de chargement des modules.';
    }
  }

  function renderModulesList(modules) {
    modulesContainer.innerHTML = '';
    if (modules.length === 0) {
      modulesContainer.textContent = 'Aucun module.';
      return;
    }
    const ul = document.createElement('ul');
    modules.forEach(mod => {
      const li = document.createElement('li');
      li.tabIndex = 0;
      li.textContent = mod.module_name;
      li.classList.add('module-name');
      li.addEventListener('click', () => selectModule(mod.module_id));
      li.addEventListener('keypress', e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectModule(mod.module_id);
        }
      });
      ul.appendChild(li);
    });
    modulesContainer.appendChild(ul);
    moduleDetails.style.display = 'none';
    testFormContainer.style.display = 'none';
  }

  function selectModule(id) {
    selectedModule = currentModules.find(m => m.module_id == id);
    if (!selectedModule) return;
    moduleTitle.textContent = selectedModule.module_name;
    renderCourses(selectedModule.courses);
    renderQuizzes(selectedModule.quizzes);
    hiddenModuleId.value = selectedModule.module_id;
    moduleDetails.style.display = 'block';
    testFormContainer.style.display = 'none';

    // Affichage bouton création selon rôle et module prof
    if (currentUserRole === ROLE_ADMIN) {
      btnCreateTest.style.display = 'inline-block';
    } else if (currentUserRole === ROLE_PROF) {
      // Le module selectionné doit appartenir au prof, donc bouton visible
      btnCreateTest.style.display = 'inline-block';
    } else {
      btnCreateTest.style.display = 'none';
    }
  }

  function renderCourses(courses) {
    coursesList.innerHTML = '';
    if (!courses || courses.length === 0) {
      const li = document.createElement('li');
      li.textContent = 'Aucun cours disponible.';
      coursesList.appendChild(li);
      return;
    }
    courses.forEach(course => {
      const li = document.createElement('li');
      li.textContent = course.title;
      li.classList.add('course-title');
      coursesList.appendChild(li);
    });
  }

  function renderQuizzes(quizzes) {
    quizzesList.innerHTML = '';
    if (!quizzes || quizzes.length === 0) {
      const li = document.createElement('li');
      li.textContent = 'Aucun test disponible.';
      quizzesList.appendChild(li);
      return;
    }
    quizzes.forEach(q => {
      const li = document.createElement('li');
      li.classList.add('test-item');
      li.innerHTML = `<h3>${escapeHtml(q.title)}</h3><small>Créé le: ${new Date(q.created_at).toLocaleDateString()}</small>`;
      quizzesList.appendChild(li);
    });
  }

  btnCreateTest.addEventListener('click', () => {
    if (!selectedModule) {
      alert('Veuillez sélectionner un module avant de créer un test.');
      return;
    }
    showTestForm();
  });

  btnAddQuestion.addEventListener('click', () => addQuestionEditor());

  btnCancelTest.addEventListener('click', () => {
    testFormContainer.style.display = 'none';
  });

  testForm.addEventListener('submit', async e => {
    e.preventDefault();
    if (!prepareAndValidateQuestions()) return;

    const formData = new FormData(testForm);
    formData.append('action', 'save_test');

    try {
      const res = await fetch('educational_platform_qcm.php', {
        method: 'POST',
        body: formData,
      });
      const data = await res.json();
      if (data.success) {
        alert('Test enregistré avec succès.');
        testFormContainer.style.display = 'none';
        loadModules();
      } else {
        alert('Erreur: ' + data.message);
      }
    } catch (err) {
      alert('Erreur de communication avec le serveur.');
    }
  });

  // Questions editor functions (identiques à précédemment)
  function addQuestionEditor(existingQ = null) {
    const container = document.createElement('div');
    container.className = 'question-item';
    container.style.marginBottom = '1em';

    const labelQ = document.createElement('label');
    labelQ.textContent = 'Question :';
    container.appendChild(labelQ);

    const textareaQ = document.createElement('textarea');
    textareaQ.rows = 3;
    textareaQ.placeholder = 'Tapez ici la question...';
    textareaQ.required = true;
    if (existingQ && existingQ.questionText) {
      textareaQ.value = existingQ.questionText;
    }
    container.appendChild(textareaQ);

    const labelImg = document.createElement('label');
    labelImg.textContent = 'Image (optionnelle) :';
    container.appendChild(labelImg);

    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = 'image/*';
    container.appendChild(inputFile);

    const imgPreview = document.createElement('img');
    imgPreview.className = 'image-preview';
    imgPreview.style.display = 'none';
    container.appendChild(imgPreview);

    inputFile.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) {
        imgPreview.style.display = 'none';
        imgPreview.src = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = function(evt) {
        imgPreview.src = evt.target.result;
        imgPreview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });

    const labelAns = document.createElement('label');
    labelAns.textContent = 'Réponses (cochez la ou les bonnes) :';
    container.appendChild(labelAns);

    const ansList = document.createElement('ul');
    ansList.style.listStyleType = 'none';
    ansList.style.paddingLeft = '0';
    container.appendChild(ansList);

    function appendAnswer(existingA = null) {
      const li = document.createElement('li');
      li.style.marginBottom = '5px';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.style.marginRight = '6px';
      if (existingA && existingA.correct) checkbox.checked = true;
      li.appendChild(checkbox);

      const inputText = document.createElement('input');
      inputText.type = 'text';
      inputText.placeholder = 'Texte de la réponse';
      inputText.required = true;
      inputText.style.width = '80%';
      if (existingA && existingA.text) inputText.value = existingA.text;
      li.appendChild(inputText);

      const btnRemove = document.createElement('button');
      btnRemove.type = 'button';
      btnRemove.textContent = 'x';
      btnRemove.style.background = '#e74c3c';
      btnRemove.style.color = 'white';
      btnRemove.style.border = 'none';
      btnRemove.style.marginLeft = '8px';
      btnRemove.style.borderRadius = '4px';
      btnRemove.style.cursor = 'pointer';
      btnRemove.addEventListener('click', () => li.remove());
      li.appendChild(btnRemove);

      ansList.appendChild(li);
    }

    if (existingQ && Array.isArray(existingQ.answers)) {
      existingQ.answers.forEach(a => appendAnswer(a));
    } else {
      appendAnswer();
      appendAnswer();
    }

    const btnAddAnswer = document.createElement('button');
    btnAddAnswer.type = 'button';
    btnAddAnswer.textContent = '+ Ajouter une réponse';
    btnAddAnswer.addEventListener('click', () => appendAnswer());
    container.appendChild(btnAddAnswer);

    const btnRemoveQuestion = document.createElement('button');
    btnRemoveQuestion.type = 'button';
    btnRemoveQuestion.textContent = 'Supprimer cette question';
    btnRemoveQuestion.style.background = '#e74c3c';
    btnRemoveQuestion.style.color = 'white';
    btnRemoveQuestion.style.border = 'none';
    btnRemoveQuestion.style.marginTop = '10px';
    btnRemoveQuestion.style.borderRadius = '6px';
    btnRemoveQuestion.style.cursor = 'pointer';
    btnRemoveQuestion.addEventListener('click', () => container.remove());
    container.appendChild(btnRemoveQuestion);

    questionsList.appendChild(container);
  }

  function prepareAndValidateQuestions() {
    const questionItems = questionsList.querySelectorAll('.question-item');
    if (questionItems.length === 0) {
      alert('Ajoutez au moins une question.');
      return false;
    }

    const questionsArray = [];

    for (const container of questionItems) {
      const questionText = container.querySelector('textarea').value.trim();
      if (!questionText) {
        alert('Chaque question doit avoir un texte.');
        return false;
      }

      const img = container.querySelector('.image-preview');
      let imageData = null;
      if (img && img.style.display === 'block') {
        imageData = img.src;
      }

      const answers = [];
      const lis = container.querySelectorAll('ul li');
      if (lis.length < 2) {
        alert('Chaque question doit avoir au moins deux réponses.');
        return false;
      }

      let oneCorrect = false;
      for (const li of lis) {
        const textInput = li.querySelector('input[type=text]');
        const checkbox = li.querySelector('input[type=checkbox]');
        const text = textInput.value.trim();
        if (!text) {
          alert('Les réponses ne doivent pas être vides.');
          return false;
        }
        if (checkbox.checked) oneCorrect = true;
        answers.push({ text: text, correct: checkbox.checked });
      }
      if (!oneCorrect) {
        alert('Au moins une réponse correcte doit être cochée par question.');
        return false;
      }

      questionsArray.push({ questionText: questionText, imageData: imageData, answers: answers });
    }

    questionsJsonInput.value = JSON.stringify(questionsArray);
    return true;
  }

  // Helper escape html
  function escapeHtml(text) {
    return text.replace(/[\"&'\/<>]/g, function (a) {
      return {
        '"': '&quot;',
        '&': '&amp;',
        "'": '&#39;',
        '/': '&#47;',
        '<': '&lt;',
        '>': '&gt;'
      }[a];
    });
  }

  window.onload = loadModules;
</script>
</body>
</html>
