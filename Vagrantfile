# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
    config.vm.box = "scotch/box"
    config.vm.box_version = "2.5"
    config.vm.network "private_network", ip: "192.168.70.10"
    config.vm.synced_folder ".", "/var/www/public", :mount_options => ["dmode=777", "fmode=666"]
end
