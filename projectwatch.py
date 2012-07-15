#!/usr/bin/env python

#######################################################################
#                                                                     #
# 2012-06-18  Katharopoulos Angelos  <katharas@gmail.com>             #
#                                                                     #
# Watches a linux filesystem for changes and executes                 #
# a command when any change happens                                   #
#                                                                     #
# TODO: make it smarter so that it can perhaps match files to a regex #
#       listen specific events i.e. only create|delete                #
#                                                                     #
#######################################################################

import threading,time
import os
import pyinotify
import argparse

# Receives all execution commands and executes them only
# if at least one second has passed since the last time it
# received that command
class CommandExecutor:
	def __init__(self):
		self.actions = set()
		self.running = False
	def addAction(self, action):
		if not self.running:
			if action in self.actions:
				self.thread.cancel()
				del self.thread
			self.actions.add(action)
			self.thread = threading.Timer(1,self.act,[action])
			self.thread.start()
	def act(self,action):
		self.running = True
		self.actions.remove(action)
		os.system(action)
		time.sleep(1)
		self.running = False

# process file system events
class ProcessProjectChanges(pyinotify.ProcessEvent):
	def __init__(self,c):
		self.command = c
		self.executor = CommandExecutor()
	def process_IN_CREATE(self, event):
		self.execute_command(event)
	def process_IN_MODIFY(self, event):
		self.execute_command(event)
	def process_IN_ATTRIB(self, event):
		self.execute_command(event)
	def process_IN_DELETE(self, event):
		self.execute_command(event)
	def process_IN_MOVED_FROM(self, event):
		self.execute_command(event)
	def process_IN_MOVED_TO(self, event):
		self.execute_command(event)
	def execute_command(self,event):
		self.executor.addAction(self.command)

# main
def projectwatch(folder, command):
	wm = pyinotify.WatchManager()
	notifier = pyinotify.Notifier(wm, ProcessProjectChanges(command))
	mask = pyinotify.EventsCodes.ALL_FLAGS['IN_DELETE'] | pyinotify.EventsCodes.ALL_FLAGS['IN_CREATE'] | pyinotify.EventsCodes.ALL_FLAGS['IN_MODIFY'] | pyinotify.EventsCodes.ALL_FLAGS['IN_MOVED_TO'] | pyinotify.EventsCodes.ALL_FLAGS['IN_MOVED_FROM']
	wdd = wm.add_watch(folder,mask,rec=True)
	while True:
		try:
			notifier.process_events()
			if notifier.check_events():
				notifier.read_events()
		except KeyboardInterrupt:
			notifier.stop();
			break;

if __name__ == '__main__':
	parser = argparse.ArgumentParser(description='Watch a directory for changes and call command')
	parser.add_argument('DIR',nargs='?',default=os.getcwd(),help='The directory to watch for changes')
	parser.add_argument('--raw',nargs='?',default=None,help='The raw command to call when a change happens')
	parser.add_argument('--script',default=os.path.dirname(os.path.realpath(__file__))+'/clsinc.php',help='The path to the php parsing script')
	parser.add_argument('-t', '--template',default=os.path.dirname(os.path.realpath(__file__))+'/simple_autoloader.php',help='The template for the autoloader')
	parser.add_argument('-o', '--output',default=os.getcwd()+'/autoloader.php',help='Where to save the autoloader')
	
	args = parser.parse_args()
	cmd = args.raw
	if cmd is None:
		cmd = '{0} -p {1} -t {2} -o {3}'.format(args.script,args.DIR,args.template,args.output)
	
	os.system(cmd)
	projectwatch(args.DIR,cmd)
