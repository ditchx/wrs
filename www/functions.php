<?php

//a. Get all patients that are taking one particular medication
function a($db, $medicationId) {
    return $db->select('patient_medication', array(
         'fields' => array(
             'DISTINCT(patient.name)',
             'patient_id',
             'medication.name AS medication',
         ),
         'joins' => array(
             array(
                 'table' => 'patient',
                 'type' => 'INNER',
                 'conditions' => array(
                     'patient.id = patient_medication.patient_id',
                     'patient_medication.deleted IS NULL',
                 ),
             ),
             array(
                 'table' => 'medication',
                 'type' => 'INNER',
                 'conditions' => array(
                     'medication.id = patient_medication.medication_id',
                     'medication.deleted IS NULL',
                 ),
             ),
         ),
         'conditions' => array(
             'patient_medication.medication_id = :medication_id',
             'patient.deleted IS NULL',
         ),
         'params' => array(
             'medication_id' => $medicationId,
         ),
     ));
}



//b. Get all patients and prescriptions count for current year
function b($db) {
    return $db->select('patient p', array(
        'fields' => array(
            'p.id AS patient_id', 
            'p.name AS patient_name', 
            'COUNT(pm.medication_id) AS prescriptions_count',
        ),
        'joins' => array(
            array(
                'table' => 'patient_medication pm',
                'type' => 'LEFT',
                 'conditions' => array(
                    'p.id = pm.patient_id',
                    'YEAR(pm.created) = YEAR(CURDATE())',
                    'pm.deleted IS NULL',
                ),
             ),
        ),
        'conditions' => array(
            'p.deleted IS NULL',
        ),
        'group' => array(
            'p.id',
            'p.name'
        ),
    ));
}

// c. Get all medications for one particular patient. 
// Returned data should include patient name, doctor name, 
// medication and prescription information
function c($db, $patientId) {
    return $db->select('patient_medication pm', array(
        'fields' => array(
            'pm.id as medication_id',
            'p.name as patient_name',
            'd.name as prescriber',
            'm.name as medication', 
            'm.dose',
            'pm.quantity',
            'pm.frequency',
            'pm.start',
            'pm.end',

        ),
        'joins' => array(
            array(
                'table' => 'patient p',
                'type' => 'LEFT',
                'conditions' => array(
                    'p.id = pm.patient_id',
                    'p.deleted IS NULL',
                ),
            ),
            array(
                'table' => 'doctor d',
                'type' => 'LEFT',
                'conditions' => array(
                    'd.id = pm.doctor_id',
                    'd.deleted IS NULL',
                ),
            ),
            array(
                'table' => 'medication m',
                'type' => 'LEFT',
                'conditions' => array(
                    'm.id = pm.medication_id',
                    'm.deleted IS NULL',
                ),
            ),
        ),
        'conditions' => array(
            'p.id = :id',
            'p.deleted IS NULL',
        ),
        'params' => array(
            'id' => $patientId
        )
    ));

}

// d. Get all patients that prescribed more than one medication for the previous and current year
function d($db) {
    return $db->select('patient p', array(
        'fields' => array(
            'p.id as patient_id',
            'p.name as patient_name',
            'COUNT(pm.medication_id) AS medication_count',
        ),
        'joins' => array(
            array(
                'table' => 'patient_medication pm',
                'type' => 'INNER',
                'conditions' => array(
                    'p.id = pm.patient_id',
                    'pm.deleted IS NULL',
                ),
            ),
        ),
        'conditions' => array(
            'YEAR(pm.created) IN (YEAR(CURDATE()), YEAR(CURDATE()) - 1)',
            'p.deleted IS NULL',
        ),
        'group' => array(
            'pm.patient_id',
        ),
        'having' => array(
            'COUNT(pm.medication_id) > 1',
        ),
        'order' => array(
            'medication_count DESC'
        ),
    ));
}

