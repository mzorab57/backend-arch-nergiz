<?php
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/response.php';

class CategoryController {
    private $category;

    public function __construct($pdo) {
        $this->category = new Category($pdo);
    }

    public function handle($method, $data) {
        switch ($method) {
            case "GET":
                jsonResponse($this->category->all());
                break;

            case "POST":
                if (!empty($data['name'])) {
                    $this->category->create($data['name']);
                    jsonResponse(["message" => "Category created"]);
                }
                jsonResponse(["error" => "Name required"], 400);
                break;

            case "PUT":
                if (!empty($data['id']) && !empty($data['name'])) {
                    $this->category->update($data['id'], $data['name']);
                    jsonResponse(["message" => "Category updated"]);
                }
                jsonResponse(["error" => "ID and Name required"], 400);
                break;

            case "DELETE":
                if (!empty($data['id'])) {
                    $this->category->delete($data['id']);
                    jsonResponse(["message" => "Category deleted"]);
                }
                jsonResponse(["error" => "ID required"], 400);
                break;

            default:
                jsonResponse(["error" => "Invalid method"], 405);
        }
    }
}