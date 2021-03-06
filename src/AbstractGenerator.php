<?php
/**
 * PHPUnit_SkeletonGenerator
 *
 * Copyright (c) 2012, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage SkeletonGenerator
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.0.0
 */

namespace SebastianBergmann\PHPUnit\SkeletonGenerator
{
    /**
     * Generator for skeletons.
     *
     * @package    PHPUnit
     * @subpackage SkeletonGenerator
     * @author     Sebastian Bergmann <sebastian@phpunit.de>
     * @copyright  2012 Sebastian Bergmann <sebastian@phpunit.de>
     * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
     * @link       http://www.phpunit.de/
     * @since      Class available since Release 1.0.0
     */
    abstract class AbstractGenerator
    {
        /**
         * @var array
         */
        protected $inClassName;

        /**
         * @var string
         */
        protected $inSourceFile;

        /**
         * @var array
         */
        protected $outClassName;

        /**
         * @var string
         */
        protected $outSourceFile;

        /**
         * Constructor.
         *
         * @param string $inClassName
         * @param string $inSourceFile
         * @param string $outClassName
         * @param string $outSourceFile
         */
        public function __construct($inClassName, $inSourceFile = '', $outClassName = '', $outSourceFile = '')
        {
            $this->inClassName = $this->parseFullyQualifiedClassName(
              $inClassName
            );

            $this->outClassName = $this->parseFullyQualifiedClassName(
              $outClassName
            );

            $this->inSourceFile = str_replace(
              $this->inClassName['fullyQualifiedClassName'],
              $this->inClassName['className'],
              $inSourceFile
            );

            $this->outSourceFile = str_replace(
              $this->outClassName['fullyQualifiedClassName'],
              $this->outClassName['className'],
              $outSourceFile
            );
        }

        /**
         * @return string
         */
        public function getOutClassName()
        {
            return $this->outClassName['fullyQualifiedClassName'];
        }

        /**
         * @return string
         */
        public function getOutSourceFile()
        {
            return $this->outSourceFile;
        }

        /**
         * Generates the code and writes it to a source file.
         *
         * @param string $file
         */
        public function write($file = '')
        {
            if ($file == '') {
                $file = $this->outSourceFile;
            }
            
            $file = str_replace('.php', '', $file); // Netbeans doesn't "regenerate" tests if file already exists.
            $file .= '.test.php';
            
            $saved = "\n";

            if (file_exists($file)) {
                $contents = file_get_contents($file);
                $start_placeholder = '/* custom tests (will be saved when test are regenerated) */';
                $end_placeholder = '/* end custom tests */';
                $start = strpos($contents, $start_placeholder);
                $end = strrpos($contents, '/* end custom tests */');
                if ($start !== false && $end !== false) {
                    $saved = substr(
                      $contents,
                      $start + strlen($start_placeholder),
                      $end - $start - strlen($start_placeholder)//- strlen($end_placeholder)
                    );
                }
            }
            $generated = $this->generate();
            $generated = str_replace('{saved}', $saved, $generated);

            file_put_contents($file, $generated);
        }

        /**
         * @param  string $className
         * @return array
         */
        protected function parseFullyQualifiedClassName($className)
        {
            $result = array(
              'namespace'               => '',
              'className'               => $className,
              'fullyQualifiedClassName' => $className
            );

            if (strpos($className, '\\') !== FALSE) {
                $tmp                 = explode('\\', $className);
                $result['className'] = $tmp[count($tmp)-1];
                $result['namespace'] = $this->arrayToName($tmp);
            }

            return $result;
        }

        /**
         * @param  array $parts
         * @return string
         */
        protected function arrayToName(array $parts)
        {
            $result = '';

            if (count($parts) > 1) {
                array_pop($parts);

                $result = join('\\', $parts);
            }

            return $result;
        }

        abstract public function generate();
    }
}
