<?php
namespace App\Controllers;

/**
 * Base controller class
 */
class Controller
{
    /**
     * Renders a template with provided data
     *
     * @param string $template Template path to render
     * @param array $data Associative array of template variables
     * @param int $status HTTP status code
     * @return void
     */
    protected function render($template, array $data = [], $status = 200)
    {
        app()->response()->status($status);

        if ($data) {
            foreach ($data as $key => $value) {
                app()->view()->setData($key, $value);
            }
        }

        app()->render($template, $data);
    }

    /**
     * Returns JSON formatted response
     *
     * @param int $status HTTP status code
     * @param mixed $data Data to be JSON encoded
     * @return \Slim\Http\Response
     */
    protected function json($status, $data)
    {
        app()->response()->status($status);
        app()->response()->header('Content-Type', 'application/json');

        return app()->response()->write(json_encode($data));
    }

    /**
     * Returns successful JSON response
     *
     * @param mixed $data Response data or message
     * @return \Slim\Http\Response
     */
    protected function success($data = [])
    {
        if ($data) {
            $data = is_array($data) ? [
                'data' => $data
            ] : [
                'message' => $data
            ];
        }

        return $this->json(200, array_merge([
            'success' => true,
        ], $data));
    }

    /**
     * Returns error JSON response
     *
     * @param string|null $message Optional error message
     * @return \Slim\Http\Response
     */
    protected function error($message = null)
    {
        if ($message) {
            $data = [
                'error' => $message
            ];
        } else {
            $data = [];
        }

        return $this->json(400, array_merge([
            'success' => false
        ], $data));
    }
}