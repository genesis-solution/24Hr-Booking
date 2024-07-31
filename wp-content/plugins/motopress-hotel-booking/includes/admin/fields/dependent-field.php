<?php

namespace MPHB\Admin\Fields;

interface DependentField {

	public function getDependencyInput();
	public function setDependencyInput( $dependencyInput );
	public function updateDependency( $dependencyValue );
}
