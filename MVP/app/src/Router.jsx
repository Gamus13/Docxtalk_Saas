import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import RegisteList from './pages/RegisteList';
import Features from './pages/features';

const router = createBrowserRouter([
  {
    path: '/app',  // Chemin racine pour votre composant principal
    element: <Features />,  // Composant LandingPage à afficher à cette route
  },
  
]);

const Router = () => (
  <RouterProvider router={router} />
);

export default Router;
