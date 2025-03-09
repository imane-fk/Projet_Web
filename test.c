void extraire_intervalle(node *root, int min, int max, int* resultat, int *taille)
{
	if(root)
	{
		if(root->data >=min && root->data <=max) 
	{	
	     *(resultat+ (*taille))=root->data;
	    *taille=*taille+1;
		 extraire_intervalle(root->left,min,max,resultat,taille);
		 extraire_intervalle(root->right,min,max,resultat,taille);
	}
	else if(root->data > max)
	{
		extraire_intervalle(root->left,min,max,resultat,taille);
	}
	else 
	 {
	   extraire_intervalle(root->right,min,max,resultat,taille);
	 }
	}

	 
}
int main()
{ 
	node *root=NULL;
	root=Add_Node(root,25);
	root=Add_Node(root,24);
	root=Add_Node(root,23);
	root=Add_Node(root,20);
	root=Add_Node(root,18);
	int tab[40] ;
	int taille=0;
	extraire_intervalle(root,20,24,tab,&taille);
	printf("Les valeurs entre [20,24] est :");
	for(int i=0;i<taille;i++)
	{
		printf("%d ",tab[i]);
	}
	return 0;
}