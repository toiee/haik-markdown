<?php
namespace Hokuken\HaikMarkdown\Plugin\Repositories;

interface PluginRepositoryInterface {

    /**
     * plugin $id is exists?
     * @params string $id plugin id
     * @return boolean
     */
    public function exists($id);

    /**
     * load Plugin by id
     * @params string $id plugin id
     * @return \Hokuken\HaikMarkdown\Plugin\PluginInterface The Plugin
     * @throws InvalidArgumentException when $id was not exist
     */
    public function load($id);

    /**
     * get all plugin list
     * @return array of plugin id
     */
    public function getAll();

}
