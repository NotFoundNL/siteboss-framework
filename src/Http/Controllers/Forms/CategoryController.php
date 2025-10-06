<?php

namespace NotFound\Framework\Http\Controllers\Forms;

use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Forms\Category;

class CategoryController extends Controller
{
    public function readAll()
    {
        return Category::get();
    }

    public function readAllBasedOnRights()
    {
        $category = new Category;

        return $category->getCategoriesByRights();
    }
}
