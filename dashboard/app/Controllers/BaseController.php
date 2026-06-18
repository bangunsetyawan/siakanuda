<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * Tahun Pelajaran aktif — tersedia di semua controller.
     *
     * @var array|null
     */
    protected $activeTP = null;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['html', 'url', 'form', 'network'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload session
        session();

        // Load Tahun Pelajaran aktif — shared ke semua controller & view
        $tpModel = new \App\Models\TahunPelajaranModel();
        $this->activeTP = $tpModel->getActive();

        // Share ke semua view secara global
        \Config\Services::renderer()->setVar('activeTahunPelajaran', $this->activeTP);
    }

    /**
     * Shortcut: ID tahun pelajaran aktif (untuk filter query).
     */
    protected function getActiveTPId(): ?int
    {
        return $this->activeTP['id'] ?? null;
    }
}
