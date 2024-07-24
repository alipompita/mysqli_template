<?php
require_once 'config.mysql.php';

function getConnection()
{
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_errno) {
        error_log("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
        die('Database connection error');
    }
    return $mysqli;
}

function query($sql, $params = [])
{
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('Prepare failed: (' . $conn->errno . ') ' . $conn->error);
        $conn->close();
        return false;
    }

    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log('Execute failed: (' . $stmt->errno . ') ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();

    return $result;
}

function execute($sql, $params = [])
{
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('Prepare failed: (' . $conn->errno . ') ' . $conn->error);
        $conn->close();
        return false;
    }

    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log('Execute failed: (' . $stmt->errno . ') ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $stmt->close();
    $conn->close();

    return true;
}

function insert_id($sql, $params = [])
{
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('Prepare failed: (' . $conn->errno . ') ' . $conn->error);
        $conn->close();
        return false;
    }

    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log('Execute failed: (' . $stmt->errno . ') ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    return $id;
}

function queryArray($sql, $params = []): ?array
{
    $result = query($sql, $params);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return null;
}

function queryList($sql, $params = [])
{
    $rows = queryArray($sql, $params);
    $items = [];
    if ($rows != null) {
        foreach ($rows as $row) {
            $items[] = $row[0];
        }
    }
    return $items;
}

function escape($value)
{
    $conn = getConnection();
    $value = $conn->real_escape_string($value);
    $conn->close();
    return $value;
}
