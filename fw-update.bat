@echo off

SET FW__MY_BRANCH=variant-1
::git -C "%~dp0lib-ace" fetch --all --tags
::git -C "%~dp0lib-ace" checkout tags/%FW__MY_BRANCH%
git checkout -b %FW__MY_BRANCH%