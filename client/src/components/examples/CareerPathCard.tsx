import CareerPathCard from '../CareerPathCard';
import { Code2, Database } from 'lucide-react';

export default function CareerPathCardExample() {
  const handleSelect = (pathId: string) => {
    console.log("Selected path:", pathId);
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">
      <CareerPathCard 
        pathId="fullstack"
        icon={Code2}
        title="Full Stack Developer"
        description="Build complete web applications from front to back"
        skills={["React", "Node.js", "PostgreSQL", "TypeScript"]}
        ranks={["Junior", "Mid-Level", "Senior", "Lead"]}
        recommended={true}
        onSelect={handleSelect}
      />
      <CareerPathCard 
        pathId="data-engineer"
        icon={Database}
        title="Data Engineer"
        description="Design and maintain data infrastructure"
        skills={["Python", "SQL", "Apache Spark", "AWS"]}
        ranks={["Junior", "Mid-Level", "Senior", "Principal"]}
        onSelect={handleSelect}
      />
    </div>
  );
}
