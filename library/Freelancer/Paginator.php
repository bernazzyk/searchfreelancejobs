<?php
class Freelancer_Paginator extends Zend_Paginator
{
    protected $groupOptions;

    protected $sortOptions;

    protected $sortDirectionOptions;

    protected $perPageOptions;

    protected $targetDiv = '';

    protected $destination;

    public $defaultPerPageOptions = array( '10' => '10', '20' => '20', '50' => '50', '100' => '100', 'all' => 'All' );

    public function getDefaultPerPageOptions( $firstOnly = false )
    {
        if ( true === $firstOnly )
        {
            $keys = array_keys( $this->defaultPerPageOptions );

            return $keys[0];
        }

        return $this->defaultPerPageOptions;
    }

    protected function _createPages($scrollingStyle = null)
    {
        $pages = parent::_createPages( $scrollingStyle );

        $pages->groupOptions = $this->groupOptions;

        $pages->sortOptions = $this->sortOptions;

        $pages->sortDirectionOptions = $this->sortDirectionOptions;

        $pages->perPageOptions = $this->perPageOptions;

        $pages->destination = $this->destination;

        $pages->targetDiv = $this->targetDiv;

        return $pages;
    }

    public function setSelectOptions( $optionName, $name, $selectedValue = null, $attributes = null, $options )
    {
            $classVar = $optionName . 'Options';

            $this->$classVar = ( object ) array
            (
                'name'       => $name,

                'selected'   => $selectedValue,

                'attributes' => $attributes,

                'options'    => $options
            );

        return $this;
    }

    public function setDestination( $destination )
    {
        $this->destination = $destination;

        return $this;
    }

    public function setTargetDiv( $targetDiv )
    {
        $this->targetDiv = $targetDiv;

        return $this;
    }

    public function setDefaultOptions( $destination, $request )
    {
        $out = $this
            ->setItemCountPerPage( $request->getParam( 'perPage', $this->getDefaultPerPageOptions( $firstOnly = true ) ) )

            ->setCurrentPageNumber( $request->getParam( 'page', 1 ) )

            ->setSelectOptions( 'perPage', 'perPage', $request->getParam( 'perPage' ), array( 'id' => $destination . '_per_page', 'class' => 'per_page', 'style' => 'width: auto' ), $this->getDefaultPerPageOptions( ) );

        return $out;
    }
}