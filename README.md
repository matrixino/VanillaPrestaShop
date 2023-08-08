# SMB Edition Fork

This is a fork of PrestaShop, kept synced with the open source project, and patched with fixes needed for PrestaShop Edition (basic + hosted) and not yet integrated into the core

## Prepare
The first time you come to this repo, you need to configure your local git  
### With a script (recommended)
You can also use the bash script like this :
```shell
configure_git.sh
```

### Manually

`git remote add upstream git@github.com:PrestaShop/PrestaShop.git`
`git remote set-url --push upstream DISABLE`

then, `git remote -v` should give you :  

`
origin  git@github.com:PrestaShopCorp/smb_edition_fork.git (fetch)
origin  git@github.com:PrestaShopCorp/smb_edition_fork.git (push)
upstream        git@github.com:PrestaShop/PrestaShop.git (fetch)
upstream        DISABLE (push)
`

Lastly, before doing anything, run `git fetch --all --prune --tags`

## Create a new version
When a new version of PrestaShop is released, a tag is created (8.0.4 for example)  
In order for Edition to build a release on top of this new core release, a branch needs to be created into this repo, based on the new tag  

### With a script (recommended)
You can also use the bash script like this :
```shell
new-version.sh 8.1.5
```
*8.1.5 need to be changed to the new version you want to create*


### Manually
For that, you need to sync the repo  
`git fetch --all --prune --tags`   

Then checkout the tag   
`git checkout 8.0.4`

Then create a new branch based on this tag   
`git switch -c edition/8.0.4`   

Then apply all commits from last edition branch   
`git cherry-pick ***`   
where *** is the hash of a commit   
note that conflicts may be needed to be resolved
