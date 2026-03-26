<?php


class Controller
{
    protected $request;
    private $view;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
    }

    public function view($file, $variables=null){
        if (empty($this->view)){
            $this->view = new View();
        }
        return $this->view->render($file,$variables);
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }


}