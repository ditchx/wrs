<?php

require 'DBConnection.php';
require 'Database.php';
require 'functions.php';

$read = new PDO("mysql:host=db;dbname=wrs", 'root', 'rootpassword');
$write = new PDO("mysql:host=db;dbname=wrs", 'root', 'rootpassword');


$connection = DBConnection::getInstance($read, $write);

$db = new Database();
$db->connect($connection);

if (isset($_POST['medication'])) {
    header('Content-Type: application/json');
    $search = trim($_POST['medication']); 

    if ($search === '') {
        echo json_encode(array());
        die();
    }

    $medication = $db->select('medication', array(
        'conditions' => array(
            'name LIKE ?'
        ),
        'fields' => array('id'),
        'params' => array($search.'%'),
        'order' => array('name ASC'),
    ));


    if ($medication) {
        $result = a($db, $medication[0]['id']);
        echo json_encode($result);
    } else {
        echo json_encode(array());
    }

    die();
}

if (isset($_POST['patient'])) {
    header('Content-Type: application/json');
    $search = trim($_POST['patient']); 

    if ($search === '') {
        echo json_encode(array());
        die();
    }

    $patient = $db->select('patient', array(
        'conditions' => array(
            'name LIKE ?'
        ),
        'fields' => array('id'),
        'params' => array($search.'%'),
        'order' => array('name ASC'),
    ));


    if ($patient) {
        $result = c($db, $patient[0]['id']);
        echo json_encode($result);
    } else {
        echo json_encode(array());
    }

    die();
}


$medicationName = 'Ramipril';
$patientName = 'Evangelina Bowlas';
$bData = b($db);
$dData = d($db);

$allMeds = $db->select('medication', array('fields' => array('name')));
$allPatients = $db->select('patient', array('fields' => array('name')));


?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"
    />
    <title>Patient Prescriptions</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>  
    </head>
  <body>
    <main class="container">
        <section x-data="taking_medication">
            <h3>a. Get all patients that are taking one particular medication</h3>
                <form>
                    <label>Medication 
                        <input list="all_medications" x-ref="search" @input.debounce="searchMedication($refs.search.value)" type="text" name="a" value="<?php echo htmlspecialchars($medicationName); ?>" />
                    </label>
                    <datalist id="all_medications">
                        <?php foreach($allMeds as $a): ?>
                        <option value="<?php echo htmlspecialchars($a['name']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <span x-show="loading" aria-busy="true">Loading</span>                    
                </form>
            <div class="overflow-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Medication</th>
                            <th scope="col">Patient ID</th>
                            <th scope="col">Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    <template x-for="patient in patients" :key="patient.patient_id">
                        <tr>
                            <td x-text="patient.medication"></td>
                            <td x-text="patient.patient_id"></td>
                            <td x-text="patient.name"></td>
                        </tr>
                    </template>                            
                    </tbody>
                </table>
            </div>
        </section>
    
        <section>
            <h3>b. Get all patients and prescriptions count for current year
            </h3>
            <div class="overflow-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Patient ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Prescription Count</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($bData as $b): ?>
                        <tr>
                            <td><?php echo $b['patient_id']; ?></td>
                            <td><?php echo htmlspecialchars($b['patient_name']); ?></td>
                            <td><?php echo $b['prescriptions_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-data="patient_medications">
            <h3>c. Get all medications for one particular patient. Returned data should include 
                patient name, doctor name, medication and prescription information
            </h3>
            <form>
                <label>Patient
                    <input list="all_patients" x-ref="search" @input.debounce="searchPatient($refs.search.value)" type="text" name="search" value="<?php echo htmlspecialchars($patientName); ?>" />
                </label>
                <datalist id="all_patients">
                    <?php foreach($allPatients as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['name']); ?>">
                    <?php endforeach; ?>
                </datalist>
                <span x-show="loading" aria-busy="true">Loading</span>                    
            </form>
                
            <div class="overflow-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Patient Name</th>
                            <th scope="col">Prescribing Provider</th>
                            <th scope="col">Medication</th>
                            <th scope="col">Dose</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Frequency</th>
                            <th scope="col">Start Date</th>
                            <th scope="col">End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <template x-for="medication in medications" :key="medication.medication_id">
                        <tr>
                            <td x-text="medication.patient_name"></td>
                            <td x-text="medication.prescriber"></td>
                            <td x-text="medication.medication"></td>
                            <td x-text="medication.dose"></td>
                            <td x-text="medication.quantity"></td>
                            <td x-text="medication.frequency"></td>
                            <td x-text="medication.start"></td>
                            <td x-text="medication.end"></td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h3>d. Get all patients that prescribed more than one medication for the previous and
                current year </h3>
            <div class="overflow-auto">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Patient ID</th>
                            <th scope="col">Patient Name</th>
                            <th scope="col">Medication Count</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($dData as $d): ?>
                        <tr>
                            <td><?php echo $d['patient_id']; ?></td>
                            <td><?php echo htmlspecialchars($d['patient_name']); ?></td>
                            <td><?php echo $d['medication_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>



        </main>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('taking_medication', () => ({
                loading: false,
                patients: [],
                searchMedication: async function(medication) {
                    this.loading = true;
                    try {
                        const response = await fetch('/', {
                            method: 'POST',
                            headers:{
                              'Content-Type': 'application/x-www-form-urlencoded'
                            },    
                            body: new URLSearchParams({
                                'medication': medication
                            })
                        });

                        this.patients = await response.json()
                    } catch(error) {
                        console.log('Error fetching items: ', error);
                    }
                    this.loading = false;
                },

                init() {
                    this.searchMedication(this.$refs.search.value);
                }

            }));

            Alpine.data('patient_medications', () => ({
                loading: false,
                medications: [],
                searchPatient: async function(patient) {
                    this.loading = true;
                    try {
                        const response = await fetch('/', {
                            method: 'POST',
                            headers:{
                              'Content-Type': 'application/x-www-form-urlencoded'
                            },    
                            body: new URLSearchParams({
                                'patient': patient
                            })
                        });

                        this.medications = await response.json()
                    } catch(error) {
                        console.log('Error fetching items: ', error);
                    }
                    this.loading = false;
                },

                init() {
                    this.searchPatient(this.$refs.search.value);
                }

            }));


        })
    </script>    
  </body>
</html>










